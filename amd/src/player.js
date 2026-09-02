// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language-exercise player.
 *
 * Drives the learner-facing attempt lifecycle through the external API: starts
 * or resumes the attempt, renders the medium and cues for the pinned version,
 * and handles answering, hints and finishing. The transcript is plain text
 * carrying solution-masked {{gap:key}} tokens; each token becomes a text
 * input, and transcript text is only ever added as text nodes, never markup.
 *
 * Answers are sent on explicit submit only (Enter or leaving the field), never
 * on every keystroke, and each submit carries the tries count the client last
 * saw so a lost-response retry is idempotent server-side. Prior input is
 * restored when an attempt is resumed.
 *
 * @module     mod_elang/player
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString, getStrings} from 'core/str';
import Log from 'core/log';
import Notification from 'core/notification';
import {
    activeCueIndex,
    autoScrollSuppressed,
    needsSeekToCue,
    pauseLandingTime,
    shouldStopAtBoundary,
} from 'mod_elang/playback';

const SELECTORS = {
    PLAYER: '[data-region="mod_elang/player"]',
    MEDIA: '[data-region="media"]',
    STATUS: '[data-region="status"]',
    TRANSCRIPT: '[data-region="transcript"]',
    CONTROLS: '[data-region="controls"]',
    SCORE: '[data-region="score"]',
    CAPTIONOVERLAY: '[data-region="captionoverlay"]',
};

/**
 * How long automatic scrolling stays out of the way after a learner scrolls.
 *
 * Long enough that reading back a few lines is not snatched away at the next
 * cue boundary, short enough that simply watching resumes on its own without
 * anything to click.
 */
const MANUAL_SCROLL_GRACE = 4000;

const GAP_TOKEN = /\{\{gap:([^}]+)\}\}/g;

// Provider refs arrive already normalised to the canonical video id by the
// server-side provider_registry (set_draft_media), so these builders only
// need to url-encode the id into the provider's embed URL. Keep this table in
// step with classes/local/media/provider_registry.php.
const PROVIDER_EMBEDS = {
    youtube: (ref) => `https://www.youtube-nocookie.com/embed/${encodeURIComponent(ref)}`,
    vimeo: (ref) => `https://player.vimeo.com/video/${encodeURIComponent(ref)}`,
};

/**
 * How a graded gap is shown.
 *
 * Each state carries a FontAwesome icon and the wording that names it. The icon
 * is what a learner reads at a glance; the wording stays in the accessible name
 * so the state is never conveyed by shape and colour alone. Spelling the result
 * out beside every gap turned a transcript into a column of sentences about
 * itself.
 */
const RESULT_STATES = {
    exact: {cls: 'mod_elang-correct', key: 'player:statecorrect', icon: 'fa-check'},
    wordrecognized: {cls: 'mod_elang-accepted', key: 'player:stateaccepted', icon: 'fa-exclamation-triangle'},
    incorrect: {cls: 'mod_elang-incorrect', key: 'player:stateincorrect', icon: 'fa-times'},
    empty: {cls: 'mod_elang-empty', key: null, icon: null},
};

const STATE_CLASSES = ['mod_elang-correct', 'mod_elang-accepted', 'mod_elang-incorrect', 'mod_elang-empty'];

// Module-level state for the single player on the page.
let attemptId = null;
const strings = {};

// In-flight submit_response promises, so finishing the attempt can wait for a
// just-typed answer that is still being sent instead of racing it.
const pendingSubmits = new Set();

/**
 * Hooks the playback flow installs once the medium is known.
 *
 * Gaps are built before there is a medium to drive, so they call through this
 * object rather than holding a reference to it. Both entries stay null when
 * the medium exposes no playback clock — a provider embed, or no medium at
 * all — and every call site tolerates that.
 */
const playbackFlow = {
    /** @type {?Function} Called with a gap wrapper after its Enter submit resolved. */
    advance: null,
    /** @type {?Function} Called with a gap wrapper when focus enters it. */
    engage: null,
};

/**
 * Call a single external function and return its promise.
 *
 * @param {String} methodname The external function name
 * @param {Object} args The call arguments
 * @returns {Promise} Resolves with the function's return value
 */
const callWs = (methodname, args) => Ajax.call([{methodname, args}])[0];

/**
 * Whether a MIME type denotes audio rather than video.
 *
 * @param {String} mime The MIME type, possibly empty
 * @returns {Boolean} True for an audio/* type
 */
const isAudioMime = (mime) => typeof mime === 'string' && mime.indexOf('audio/') === 0;

/**
 * Build a provider embed iframe, or null for an unsupported provider.
 *
 * @param {Object} media The media descriptor
 * @returns {Element|null} The iframe, or null when the provider is unknown
 */
const buildProviderEmbed = (media) => {
    const builder = PROVIDER_EMBEDS[media.provider];
    if (!builder) {
        return null;
    }
    const iframe = document.createElement('iframe');
    iframe.src = builder(media.providerref);
    iframe.title = media.provider;
    iframe.className = 'mod_elang-embed';
    iframe.setAttribute('allowfullscreen', 'allowfullscreen');
    iframe.setAttribute('loading', 'lazy');
    return iframe;
};

/**
 * Build an audio or video element with a poster and preload hint.
 *
 * @param {Boolean} audio Whether to build an audio element
 * @param {String} posterurl The poster URL, used for video only
 * @returns {Element} The media element
 */
const buildMediaElement = (audio, posterurl) => {
    const element = document.createElement(audio ? 'audio' : 'video');
    element.controls = true;
    element.preload = 'metadata';
    if (!audio && posterurl) {
        element.setAttribute('poster', posterurl);
    }
    return element;
};

/**
 * Build a media element for file-kind media, with one source per encoding.
 *
 * @param {Object} media The media descriptor
 * @returns {Element} The media element
 */
const buildFileMedia = (media) => {
    const audio = isAudioMime(media.mimetype)
        || (media.files.length > 0 && isAudioMime(media.files[0].mimetype));
    const element = buildMediaElement(audio, media.posterurl);
    media.files.forEach((file) => {
        const source = document.createElement('source');
        source.src = file.url;
        if (file.mimetype) {
            source.type = file.mimetype;
        }
        element.appendChild(source);
    });
    return element;
};

/**
 * Build a media element for a direct URL medium.
 *
 * @param {Object} media The media descriptor
 * @returns {Element} The media element
 */
const buildUrlMedia = (media) => {
    const element = buildMediaElement(isAudioMime(media.mimetype), media.posterurl);
    element.src = media.url;
    return element;
};

/**
 * Warn when the browser cannot decode the video track of a medium.
 *
 * A codec the browser does not support (for example MPEG-4 Part 2 from the
 * DivX/Xvid era) typically still plays its audio track while the picture stays
 * black — VLC and other desktop players decode it fine, so the file looks
 * healthy to the author. The reliable runtime signal is a VIDEO element whose
 * metadata reports a zero-width video track. Shows one dismissible warning
 * above the medium instead of leaving the learner with a silent black frame.
 *
 * @param {Element} element The media element that was mounted
 * @param {Element} region The media region to place the warning in
 */
const watchVideoDecoding = (element, region) => {
    if (!element || element.tagName !== 'VIDEO') {
        return;
    }
    let warned = false;
    const check = () => {
        if (warned || element.videoWidth > 0) {
            return;
        }
        // An audio file mounted in a video element also reports no picture
        // size; that is not a decoding problem, so it must not warn.
        const src = element.currentSrc || '';
        if (/\.(mp3|m4a|aac|oga|ogg|opus|wav|flac)(\?|#|$)/i.test(src)) {
            return;
        }
        // Metadata is loaded (readyState >= 1) yet there is no picture size:
        // the video track cannot be decoded by this browser.
        if (element.readyState >= 1) {
            warned = true;
            const notice = document.createElement('div');
            notice.className = 'alert alert-warning mod_elang-novideo';
            notice.setAttribute('role', 'alert');
            notice.textContent = strings['player:novideotrack'];
            region.insertBefore(notice, element);
        }
    };
    element.addEventListener('loadedmetadata', check);
    element.addEventListener('playing', check);
};

/**
 * Keep interactive captions visible in fullscreen.
 *
 * The native fullscreen button belongs to the media element, and a fullscreened
 * media element is drawn alone: its siblings — including the caption overlay
 * with the gaps in it — are simply not there. Fullscreening the stage instead
 * takes the overlay along, and the browser draws the same controls.
 *
 * Rather than hiding the native control and offering a replacement, this
 * listens for the medium entering fullscreen and moves the request up to the
 * stage. The swap happens inside the user gesture that started it, which is
 * what browsers require. Where it is refused — notably iOS, whose fullscreen is
 * a system player that cannot contain HTML — the medium simply plays
 * fullscreen without captions and the exercise continues unharmed on exit.
 *
 * @param {Element} stage The positioned wrapper holding medium and overlay
 * @param {Element} element The media element
 * @returns {void}
 */
const attachFullscreenRedirect = (stage, element) => {
    if (typeof stage.requestFullscreen !== 'function') {
        return;
    }

    let redirecting = false;

    document.addEventListener('fullscreenchange', () => {
        if (redirecting || document.fullscreenElement !== element) {
            return;
        }

        redirecting = true;
        Promise.resolve(document.exitFullscreen())
            .then(() => stage.requestFullscreen())
            .catch((error) => Log.debug(error))
            .then(() => {
                redirecting = false;
                return null;
            })
            .catch(() => null);
    });
};

/**
 * Render the attempt's medium into the media region.
 *
 * @param {Element} region The media region element
 * @param {Object} media The media descriptor from get_attempt_exercise
 * @param {String} position The effective subtitle position: below, overlaytop or overlaybottom
 * @returns {Element|null} The media element created, or null if none
 */
const renderMedia = (region, media, position) => {
    region.textContent = '';
    let element = null;
    if (media.kind === 'provider') {
        element = buildProviderEmbed(media);
    } else if (media.kind === 'file') {
        element = buildFileMedia(media);
    } else if (media.kind === 'url') {
        element = buildUrlMedia(media);
    }
    if (!element) {
        return null;
    }

    if (position === 'overlaytop' || position === 'overlaybottom') {
        // A positioned wrapper so the caption can sit over the picture. The
        // overlay is a sibling of the medium, never a child: a media element
        // may not contain flow content, and the gaps inside the caption have
        // to stay real focusable inputs.
        const stage = document.createElement('div');
        stage.className = 'mod_elang-media-stage';
        stage.appendChild(element);

        const overlay = document.createElement('div');
        overlay.className = 'mod_elang-caption-overlay mod_elang-caption-' + position;
        overlay.dataset.region = 'captionoverlay';
        stage.appendChild(overlay);

        region.appendChild(stage);
        attachFullscreenRedirect(stage, element);
    } else {
        region.appendChild(element);
    }

    watchVideoDecoding(element, region);

    return element;
};

/**
 * Reflect a graded result on a gap: its state class and the accessible status
 * text, keeping any "hint used" marker.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} state The gap's status element
 * @param {String} resultstate One of exact, wordrecognized, incorrect, empty
 * @returns {void}
 */
const applyResultState = (wrap, state, resultstate) => {
    const info = RESULT_STATES[resultstate] || RESULT_STATES.empty;
    STATE_CLASSES.forEach((cls) => wrap.classList.remove(cls));
    wrap.classList.add(info.cls);

    const parts = [];
    if (info.key) {
        parts.push(strings[info.key]);
    }
    if (wrap.dataset.hintlevel !== '0') {
        parts.push(strings['player:statehinted']);
    }
    const label = parts.join(' — ');

    state.textContent = '';
    if (info.icon === null) {
        state.removeAttribute('title');
        return;
    }

    const icon = document.createElement('i');
    icon.className = 'fa ' + info.icon;
    icon.setAttribute('aria-hidden', 'true');
    state.appendChild(icon);

    // The wording is not dropped, only moved out of the line of text: screen
    // readers announce it through the live region, and it is the tooltip.
    const sr = document.createElement('span');
    sr.className = 'sr-only visually-hidden';
    sr.textContent = label;
    state.appendChild(sr);
    state.setAttribute('title', label);
};

/**
 * Submit a gap's current value, unless it is empty, and reflect the result.
 * Sends the tries count last seen so a lost-response retry is idempotent. The
 * in-flight promise is tracked so finishing the attempt can wait for it.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} input The gap input
 * @param {Element} state The gap's status element
 * @returns {Promise} Resolves once the result has been applied
 */
const submitGap = (wrap, input, state) => {
    const value = input.value.trim();
    if (value === '' || wrap.dataset.submitting === '1') {
        return Promise.resolve();
    }
    wrap.dataset.submitting = '1';
    const run = async() => {
        try {
            const result = await callWs('mod_elang_submit_response', {
                attemptid: attemptId,
                gapid: parseInt(wrap.dataset.gapid, 10),
                responsetext: value,
                expectedtries: parseInt(wrap.dataset.tries, 10),
            });
            wrap.dataset.tries = String(parseInt(wrap.dataset.tries, 10) + 1);
            applyResultState(wrap, state, result.resultstate);
            updateScore(result);
        } catch (error) {
            Log.error(error);
            state.textContent = error.message || strings['player:submitfailed'];
        } finally {
            wrap.dataset.submitting = '0';
        }
    };
    const tracked = run();
    pendingSubmits.add(tracked);
    return tracked.finally(() => pendingSubmits.delete(tracked));
};

/**
 * Reveal the next hint level for a gap and mark it hint-used.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} input The gap input
 * @param {Element} state The gap's status element
 * @returns {Promise} Resolves once the hint has been shown
 */
const requestHint = async(wrap, input, state) => {
    try {
        const hint = await callWs('mod_elang_request_hint', {
            attemptid: attemptId,
            gapid: parseInt(wrap.dataset.gapid, 10),
            expectedlevel: parseInt(wrap.dataset.hintlevel, 10),
        });
        wrap.dataset.hintlevel = String(hint.level);
        wrap.classList.add('mod_elang-hinted');
        state.textContent = `${strings['player:statehinted']}: ${hint.hinttext}`;
        updateScore(hint);
        input.focus();
    } catch (error) {
        Log.error(error);
        state.textContent = error.message || strings['player:submitfailed'];
    }
};

/**
 * Update the score region from an attempt aggregate payload that carries a
 * score fraction.
 *
 * @param {Object} payload A payload with a score field (0..1)
 * @returns {void}
 */
const updateScore = (payload) => {
    const region = document.querySelector(SELECTORS.SCORE);
    if (region && typeof payload.score === 'number') {
        region.textContent = strings['player:scorelabel'].replace('%score%', Math.round(payload.score * 100));
    }
};

/**
 * Build the explicit submit ("check answer") button for a gap. Pressing Enter
 * and leaving the field still submit; this adds the visible, unambiguous submit
 * action the blueprint requires.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} input The gap input
 * @param {Element} state The gap's status element
 * @returns {Element} The submit button
 */
const buildSubmitButton = (wrap, input, state) => {
    const button = buildIconButton('mod_elang-gapsubmit', 'fa-check-circle', strings['player:check']);
    button.addEventListener('click', () => submitGap(wrap, input, state));
    return button;
};

/**
 * Build a quiet icon button carrying its wording as its accessible name.
 *
 * Two words of link text beside every gap add up: on a transcript of forty
 * cues, "Check answer" and "Show hint" were most of what was on the page. The
 * wording is not lost — it is the accessible name and the tooltip — it is
 * simply no longer competing with the sentence the exercise is about.
 *
 * @param {String} cls The button's own class
 * @param {String} icon The FontAwesome icon class
 * @param {String} label The accessible name and tooltip
 * @returns {Element} The button
 */
const buildIconButton = (cls, icon, label) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = cls + ' btn btn-link btn-sm mod_elang-iconbtn';
    button.setAttribute('aria-label', label);
    button.setAttribute('title', label);

    const glyph = document.createElement('i');
    glyph.className = 'fa ' + icon;
    glyph.setAttribute('aria-hidden', 'true');
    button.appendChild(glyph);

    return button;
};

/**
 * Build the hint button for a gap.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} input The gap input
 * @param {Element} state The gap's status element
 * @returns {Element} The hint button
 */
const buildHintButton = (wrap, input, state) => {
    const button = buildIconButton('mod_elang-hintbtn', 'fa-lightbulb-o', strings['player:hint']);
    button.addEventListener('click', () => requestHint(wrap, input, state));
    return button;
};

/**
 * Build a gap: a wrapper holding the input, an aria-live status, an explicit
 * submit button and a hint button, plus an optional associated link. Answers
 * are sent on Enter, via the submit button, or on leaving the field.
 *
 * @param {Object|undefined} gap The gap record, if the token resolved to one
 * @param {String} label The accessible label for the input
 * @returns {Element} The gap wrapper
 */
const buildGap = (gap, label) => {
    const wrap = document.createElement('span');
    wrap.className = 'mod_elang-gapwrap';

    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'mod_elang-gap';
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('aria-label', label);

    const state = document.createElement('span');
    state.className = 'mod_elang-gapstate';
    state.setAttribute('role', 'status');
    state.setAttribute('aria-live', 'polite');

    if (!gap) {
        input.disabled = true;
        wrap.appendChild(input);
        wrap.appendChild(state);
        return wrap;
    }

    wrap.dataset.gapid = gap.id;
    wrap.dataset.tries = '0';
    wrap.dataset.hintlevel = '0';
    wrap.dataset.submitting = '0';
    if (gap.maxlength > 0) {
        input.maxLength = gap.maxlength;
    }

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            // Bound to the submit's own promise, not fired alongside it: moving
            // the focus away triggers the blur handler, and without waiting the
            // same answer would be sent twice.
            submitGap(wrap, input, state).then(() => {
                if (playbackFlow.advance) {
                    playbackFlow.advance(wrap);
                }
                return null;
            }).catch(Log.error);
        }
    });
    input.addEventListener('focus', () => {
        if (playbackFlow.engage) {
            playbackFlow.engage(wrap);
        }
    });
    input.addEventListener('blur', (event) => {
        // Leaving the field to click this gap's own submit or hint button is
        // not a separate submit — that button's own handler covers it.
        const related = event.relatedTarget;
        const isowncontrol = !!related && (related.classList.contains('mod_elang-hintbtn')
            || related.classList.contains('mod_elang-gapsubmit'));
        if (isowncontrol) {
            return;
        }
        submitGap(wrap, input, state);
    });

    wrap.appendChild(input);
    wrap.appendChild(state);
    wrap.appendChild(buildSubmitButton(wrap, input, state));
    wrap.appendChild(buildHintButton(wrap, input, state));
    if (gap.linkurl) {
        const link = document.createElement('a');
        link.className = 'mod_elang-gaplink';
        link.href = gap.linkurl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = strings['player:gaplink'];
        link.setAttribute('aria-label', `${strings['player:gaplink']}: ${label}`);
        wrap.appendChild(link);
    }
    return wrap;
};

/**
 * Append a cue's transcript to a list item, replacing each {{gap:key}} token
 * with a gap and everything else with text nodes.
 *
 * @param {Element} item The list item to append into
 * @param {String} transcript The masked transcript text
 * @param {Object} gapsByKey Map of gapkey to gap record
 * @param {Function} nextLabel Returns the next gap's accessible label
 * @returns {void}
 */
const appendTranscript = (item, transcript, gapsByKey, nextLabel) => {
    let lastindex = 0;
    let match;
    GAP_TOKEN.lastIndex = 0;
    while ((match = GAP_TOKEN.exec(transcript)) !== null) {
        const before = transcript.slice(lastindex, match.index);
        if (before) {
            item.appendChild(document.createTextNode(before));
        }
        item.appendChild(buildGap(gapsByKey[match[1]], nextLabel()));
        lastindex = GAP_TOKEN.lastIndex;
    }
    const rest = transcript.slice(lastindex);
    if (rest) {
        item.appendChild(document.createTextNode(rest));
    }
};

/**
 * Append a page of cues to the transcript list, each as a line with gaps in
 * place and carrying its start/end time (milliseconds) for synchronisation.
 *
 * @param {Element} list The transcript list element
 * @param {Array} cues The cues from get_attempt_cues
 * @param {Function} nextLabel Returns the next gap's accessible label
 * @returns {void}
 */
const appendCues = (list, cues, nextLabel) => {
    cues.forEach((cue) => {
        const item = document.createElement('li');
        item.className = 'mod_elang-cue';
        item.dataset.cueid = cue.id;
        item.dataset.starttime = cue.starttime;
        item.dataset.endtime = cue.endtime;

        const gapsByKey = {};
        cue.gaps.forEach((gap) => {
            gapsByKey[gap.gapkey] = gap;
        });

        appendTranscript(item, cue.transcript, gapsByKey, nextLabel);
        list.appendChild(item);
    });
};

/**
 * Load every cue page for the attempt and append them all, so the whole
 * transcript is present for synchronisation rather than only the first page.
 *
 * @param {Element} list The transcript list element
 * @param {Number} totalcues The total number of cues in the version
 * @param {Function} nextLabel Returns the next gap's accessible label
 * @returns {Promise} Resolves once every page has been appended
 */
const loadAllCues = async(list, totalcues, nextLabel) => {
    const limit = 50;
    let offset = 0;
    while (offset < totalcues) {
        const page = await callWs('mod_elang_get_attempt_cues', {attemptid: attemptId, offset, limit});
        if (page.cues.length === 0) {
            break;
        }
        appendCues(list, page.cues, nextLabel);
        offset += page.cues.length;
    }
};

/**
 * Wire the keyboard flow and the cue-boundary behaviour to the medium.
 *
 * Two things that look separate but share one question — which cue is being
 * worked on:
 *
 * - Enter checks the answer and moves to the next gap. When that gap belongs
 *   to another cue, playback jumps there and runs to that cue's end marker.
 * - Whether playback stops at a cue's end depends on the mode: "stop" always,
 *   "nostop" never, and "auto" only while that cue is the one being worked on
 *   — clicked, or holding the keyboard focus in one of its gaps.
 *
 * @param {HTMLMediaElement} mediaEl The medium being played
 * @param {Element} list The cue list
 * @param {String} mode The effective pause mode: auto, stop or nostop
 * @returns {void}
 */
const attachPlaybackFlow = (mediaEl, list, mode) => {
    const items = Array.from(list.querySelectorAll('.mod_elang-cue'));

    /**
     * Whether a cue still has something to answer.
     *
     * A cue whose gaps are all filled in is finished work: holding playback at
     * its end would ask the learner to press play again for nothing.
     *
     * @param {Element} cue The cue element
     * @returns {Boolean} True while at least one of its gaps is empty
     */
    const hasOpenGaps = (cue) => Array.from(cue.querySelectorAll('.mod_elang-gapwrap[data-gapid] input'))
        .some((input) => input.value.trim() === '');

    /** @type {?Element} The cue currently being worked on, for mode "auto". */
    let engaged = null;
    /** @type {?Element} The cue we are inside of, to notice crossing its end. */
    let inside = null;
    /** @type {?String} The cue we already stopped for, so play() gets past it. */
    let stoppedfor = null;

    const startOf = (cue) => parseFloat(cue.dataset.starttime);
    const endOf = (cue) => parseFloat(cue.dataset.endtime);
    const bounds = items.map((item) => ({starttime: startOf(item), endtime: endOf(item)}));
    const cueAt = (ms) => {
        const index = activeCueIndex(bounds, ms);
        return index >= 0 ? items[index] : null;
    };

    /**
     * Every gap of the exercise in cue order.
     *
     * Read from the cue list rather than from document order: in the overlay
     * modes the active cue lives over the medium, outside the list, and its
     * gaps would otherwise sort as if they came first.
     *
     * @returns {Element[]} The gap wrappers, in reading order
     */
    const gapsInOrder = () => {
        const gaps = [];
        items.forEach((item) => {
            item.querySelectorAll('.mod_elang-gapwrap[data-gapid]').forEach((gap) => gaps.push(gap));
        });
        return gaps;
    };

    playbackFlow.engage = (wrap) => {
        engaged = wrap.closest('.mod_elang-cue');
    };

    playbackFlow.advance = (wrap) => {
        const gaps = gapsInOrder();
        // Skip past anything already answered: Enter means "on to the next
        // thing to do", and stopping on a filled gap would make the learner
        // press it again for every word they had got right.
        const next = gaps.slice(gaps.indexOf(wrap) + 1)
            .find((candidate) => {
                const input = candidate.querySelector('input');
                return input !== null && input.value.trim() === '';
            });
        if (!next) {
            return;
        }

        const input = next.querySelector('input');
        if (input) {
            input.focus();
        }

        const cue = next.closest('.mod_elang-cue');
        if (!cue) {
            return;
        }
        engaged = cue;

        // Only seek when playback is not already inside that cue; otherwise a
        // second gap in the same cue would rewind the sentence being heard.
        const ms = mediaEl.currentTime * 1000;
        if (needsSeekToCue({starttime: startOf(cue), endtime: endOf(cue)}, ms)) {
            mediaEl.currentTime = startOf(cue) / 1000;
        }
        stoppedfor = null;
        const played = mediaEl.play();
        if (played && typeof played.catch === 'function') {
            // Autoplay can be refused; that is not an error worth showing.
            played.catch(() => null);
        }
    };

    items.forEach((item) => {
        item.addEventListener('click', () => {
            engaged = item;
        });
    });

    // A seek is the learner choosing a position, which ends whatever was being
    // worked on before it unless they land back in the same cue.
    mediaEl.addEventListener('seeked', () => {
        const cue = cueAt(mediaEl.currentTime * 1000);
        if (cue !== engaged) {
            engaged = null;
        }
        inside = cue;
        stoppedfor = null;
    });

    // Resuming means "carry on past this boundary", so the cue we stopped for
    // stops counting.
    mediaEl.addEventListener('play', () => {
        stoppedfor = null;
    });

    mediaEl.addEventListener('timeupdate', () => {
        if (mode === 'nostop' || mediaEl.paused) {
            return;
        }

        const ms = mediaEl.currentTime * 1000;
        const cue = cueAt(ms);

        if (inside && cue !== inside && ms >= endOf(inside)) {
            const shouldstop = shouldStopAtBoundary({
                mode,
                engaged: engaged === inside,
                hasopengaps: hasOpenGaps(inside),
            });
            const crossed = inside;
            inside = cue;

            if (shouldstop && stoppedfor !== crossed.dataset.cueid) {
                stoppedfor = crossed.dataset.cueid;
                mediaEl.pause();
                // The timeupdate event fires only a few times a second, so
                // playback is already a fraction past the boundary. Landing
                // just inside the cue that was crossed keeps the next resume
                // from skipping the first word of the following cue, and keeps
                // that cue the active one — parking exactly on the edge would
                // leave no cue active at all and blank an overlay caption.
                mediaEl.currentTime = pauseLandingTime({endtime: endOf(crossed)}) / 1000;
            }
            return;
        }

        inside = cue;
    });
};

/**
 * Keep the visible cue in step with the medium, and place it according to the
 * subtitle position.
 *
 * This is the single source of truth for which cue is active; the three
 * positions differ only in where that cue is put, never in how it is built.
 *
 * @param {HTMLMediaElement} mediaEl The medium being played
 * @param {Element} list The cue list
 * @param {String} position The effective subtitle position: below, overlaytop or overlaybottom
 * @param {Element|null} overlay The caption overlay, or null below the medium
 * @returns {void}
 */
const attachSync = (mediaEl, list, position, overlay) => {
    const items = Array.from(list.querySelectorAll('.mod_elang-cue'));
    const overlaymode = overlay !== null;
    let current = null;

    // Auto-scroll suppression. Our own scrollIntoView() also fires a scroll
    // event, so a flag distinguishes it from a learner reaching for the
    // scrollbar; without that the first automatic scroll would suppress every
    // one after it.
    let selfscrolling = false;
    let suppressuntil = 0;

    if (!overlaymode) {
        list.parentElement.addEventListener('scroll', () => {
            if (selfscrolling) {
                return;
            }
            suppressuntil = Date.now() + MANUAL_SCROLL_GRACE;
        }, {passive: true});
    }

    const scrollToCurrent = () => {
        if (autoScrollSuppressed(Date.now(), suppressuntil)) {
            return;
        }
        selfscrolling = true;
        current.scrollIntoView({block: 'nearest', behavior: 'smooth'});
        // Long enough to cover the smooth scroll the call above starts.
        window.setTimeout(() => {
            selfscrolling = false;
        }, 700);
    };

    /**
     * Show a cue as the active one, moving it into the overlay in overlay modes.
     *
     * The very same element is moved, not a copy: it already carries the gap
     * inputs with their restored values, their event listeners and their
     * graded state. Rendering a second copy would mean two gap implementations
     * that could disagree about what the learner has typed.
     *
     * @param {Element|null} active The cue to activate, or null for none
     * @returns {void}
     */
    const activate = (active) => {
        // Between two cues there is no active one, and pausing at a boundary
        // lands exactly there. Clearing the overlay then would take the
        // sentence off the screen at the very moment the learner is asked to
        // fill it in — the caption stays until another cue replaces it.
        if (active === null && overlaymode) {
            return;
        }

        if (current) {
            current.classList.remove('mod_elang-current');
            current.removeAttribute('aria-current');
            if (overlaymode && current.parentElement === overlay) {
                // Back to its anchor, so the transcript keeps its order for
                // when the mode or the medium changes under it.
                const anchor = list.querySelector('[data-anchorfor="' + current.dataset.cueid + '"]');
                if (anchor) {
                    anchor.parentElement.replaceChild(current, anchor);
                }
            }
        }

        current = active;

        if (!current) {
            return;
        }

        current.classList.add('mod_elang-current');
        current.setAttribute('aria-current', 'true');

        if (overlaymode) {
            const anchor = document.createElement('li');
            anchor.className = 'mod_elang-cue-anchor';
            anchor.dataset.anchorfor = current.dataset.cueid;
            current.parentElement.replaceChild(anchor, current);
            overlay.textContent = '';
            overlay.appendChild(current);
        } else {
            scrollToCurrent();
        }
    };

    const bounds = items.map((item) => ({
        starttime: parseFloat(item.dataset.starttime),
        endtime: parseFloat(item.dataset.endtime),
    }));

    const syncToTime = () => {
        const index = activeCueIndex(bounds, mediaEl.currentTime * 1000);
        const active = index >= 0 ? items[index] : null;
        if (active !== current) {
            activate(active);
        }
    };

    mediaEl.addEventListener('timeupdate', syncToTime);
    // A seek while paused produces no timeupdate in every browser, so the
    // visible cue would lag behind the position the learner just chose.
    mediaEl.addEventListener('seeked', syncToTime);

    items.forEach((item) => {
        item.addEventListener('click', (event) => {
            if (event.target.closest('.mod_elang-gapwrap')) {
                return;
            }
            mediaEl.currentTime = parseFloat(item.dataset.starttime) / 1000;
        });
    });
};

/**
 * Finish the attempt, lock the player and show the final score.
 *
 * @param {Element} player The player container
 * @returns {Promise} Resolves once the attempt is finished
 */
const finishAttempt = async(player) => {
    // Wait for any in-flight answer submissions first, so an answer the learner
    // just typed and is still sending cannot lose a race with finishing and be
    // rejected as "attempt already finished".
    if (pendingSubmits.size > 0) {
        await Promise.allSettled([...pendingSubmits]);
    }

    const result = await callWs('mod_elang_finish_attempt', {attemptid: attemptId});

    player.querySelectorAll('.mod_elang-gap, .mod_elang-hintbtn, .mod_elang-finishbtn')
        .forEach((element) => {
            element.disabled = true;
        });

    const score = Math.round(result.score * 100);
    const status = player.querySelector(SELECTORS.STATUS);
    if (status) {
        status.textContent = strings['player:finished'].replace('%score%', score);
    }
    updateScore(result);
};

/**
 * Build the finish button and place it in the controls region.
 *
 * @param {Element} player The player container
 * @returns {void}
 */
const renderControls = (player) => {
    const controls = player.querySelector(SELECTORS.CONTROLS);
    if (!controls) {
        return;
    }
    controls.textContent = '';

    // How far along the attempt is, next to the button that ends it. Finishing
    // is irreversible, and the question it really asks — "have I answered
    // everything?" — was one the page did not answer.
    const progress = document.createElement('span');
    progress.className = 'mod_elang-progress mr-3 me-3';
    progress.dataset.region = 'progress';
    progress.setAttribute('role', 'status');
    progress.setAttribute('aria-live', 'polite');

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'mod_elang-finishbtn btn btn-primary';
    button.textContent = strings['player:finish'];

    /**
     * Count the gaps and how many of them are still empty.
     *
     * @returns {{total: Number, open: Number}} The counts
     */
    const countGaps = () => {
        const inputs = Array.from(player.querySelectorAll('.mod_elang-gapwrap[data-gapid] input'));
        return {
            total: inputs.length,
            open: inputs.filter((input) => input.value.trim() === '').length,
        };
    };

    const refresh = () => {
        const {total, open} = countGaps();
        if (total === 0) {
            progress.textContent = '';
            return;
        }
        progress.textContent = strings['player:progress']
            .replace('{$a->done}', String(total - open))
            .replace('{$a->total}', String(total));
        // Complete is the normal way to finish, so the button says so and
        // leads; incomplete stays possible, because an exercise nobody can
        // hand in unfinished is one people abandon instead.
        button.classList.toggle('btn-primary', open === 0);
        button.classList.toggle('btn-outline-primary', open !== 0);
    };

    const finish = () => finishAttempt(player).catch((error) => {
        Log.error(error);
        const status = player.querySelector(SELECTORS.STATUS);
        if (status) {
            status.textContent = error.message || strings['player:submitfailed'];
        }
    });

    button.addEventListener('click', () => {
        const {open} = countGaps();

        // Confirmed only when something is still empty. Asking every time
        // trains people to click through the question.
        if (open === 0) {
            finish();
            return;
        }

        // Moodle's own dialogue rather than window.confirm(): a native confirm
        // is unthemed, unstyled, cannot carry a translated button label, and
        // returns focus nowhere in particular. Cancelling rejects the promise,
        // which is the ordinary answer here and not an error.
        Notification.saveCancelPromise(
            strings['player:finish'],
            strings['player:finishincomplete'].replace('{$a}', String(open)),
            strings['player:finish'],
            {triggerElement: button}
        ).then(finish).catch(() => null);
    });

    // The count follows what is typed, not only what has been submitted: a
    // learner who filled the last gap should see that before they press.
    player.addEventListener('input', (event) => {
        if (event.target.closest('.mod_elang-gapwrap')) {
            refresh();
        }
    });

    controls.appendChild(progress);
    controls.appendChild(button);
    refresh();
};

/**
 * Load and cache the player's UI strings.
 *
 * @returns {Promise} Resolves once strings are cached
 */
const loadStrings = async() => {
    const keys = [
        'player:gaplabel', 'player:gaplink', 'player:check', 'player:hint', 'player:finish', 'player:finished',
        'player:finishincomplete', 'player:progress',
        'player:statecorrect', 'player:stateaccepted', 'player:stateincorrect',
        'player:statehinted', 'player:submitfailed', 'player:scorelabel', 'player:ready',
        'player:novideotrack', 'player:outdatedattempt',
    ];
    const values = await getStrings(keys.map((key) => ({key, component: 'mod_elang'})));
    keys.forEach((key, index) => {
        strings[key] = values[index];
    });
};

/**
 * Restore the learner's own saved state into the rendered gaps: their
 * previously typed text, tries count, hint level and the graded result — so a
 * reload mid-attempt continues where they left off rather than starting blank.
 *
 * @param {Element} list The transcript list element
 * @returns {Promise} Resolves once the saved state has been applied
 */
const restoreState = async(list) => {
    const state = await callWs('mod_elang_get_attempt_state', {attemptid: attemptId});

    state.responses.forEach((response) => {
        const wrap = list.querySelector(`.mod_elang-gapwrap[data-gapid="${response.gapid}"]`);
        if (!wrap) {
            return;
        }
        const input = wrap.querySelector('.mod_elang-gap');
        const gapstate = wrap.querySelector('.mod_elang-gapstate');

        wrap.dataset.tries = String(response.tries);
        wrap.dataset.hintlevel = String(response.hintlevel);
        if (response.hintlevel > 0) {
            wrap.classList.add('mod_elang-hinted');
        }
        if (response.responsetext !== '') {
            input.value = response.responsetext;
        }

        if (response.tries > 0) {
            applyResultState(wrap, gapstate, response.resultstate);
        } else if (response.hintlevel > 0) {
            gapstate.textContent = strings['player:statehinted'];
        }
    });

    updateScore(state);
};

/**
 * Start or resume the attempt and render its medium, cues and controls.
 *
 * @param {Number} cmid The course module id
 * @param {Element} player The player container
 * @returns {Promise} Resolves when the initial render is complete
 */
const bootstrap = async(cmid, player) => {
    await loadStrings();

    const attempt = await callWs('mod_elang_start_attempt', {cmid});
    attemptId = attempt.attemptid;

    const exercise = await callWs('mod_elang_get_attempt_exercise', {attemptid: attemptId});

    // The effective position, not the stored one: the server has already
    // resolved what this medium can honour, so the client never has to decide
    // whether an audio track can carry an overlay.
    const playback = exercise.playback || {};
    const position = playback.effectivesubtitleposition || 'below';
    const overlaymode = position === 'overlaytop' || position === 'overlaybottom';

    const mediaregion = player.querySelector(SELECTORS.MEDIA);
    const mediaEl = renderMedia(mediaregion, exercise.media, position);
    player.classList.add('mod_elang-position-' + position);

    if (exercise.outdated) {
        // The exercise was republished after this attempt was touched; the
        // attempt deliberately continues on the content it started with.
        const notice = document.createElement('div');
        notice.className = 'alert alert-info mod_elang-outdated';
        notice.setAttribute('role', 'status');
        notice.textContent = strings['player:outdatedattempt'];
        player.insertBefore(notice, player.firstChild);
    }

    const transcriptregion = player.querySelector(SELECTORS.TRANSCRIPT);
    transcriptregion.textContent = '';
    // The bounded, self-scrolling region only exists below the medium. In an
    // overlay mode the transcript still holds every cue — the active one is
    // moved out and back — but it is not the reading surface, so bounding it
    // would only add a scrollbar next to an empty-looking list.
    transcriptregion.classList.toggle('mod_elang-transcript-scroll', !overlaymode);
    // Not shown twice: with the caption over the picture, repeating the whole
    // transcript underneath would put the same gaps on the page in two places
    // and leave the learner unsure which one counts. The list still exists —
    // the active cue is moved out of it and back — it is simply not on screen.
    transcriptregion.classList.toggle('mod_elang-transcript-hidden', overlaymode);
    const list = document.createElement('ol');
    list.className = 'mod_elang-cues';
    transcriptregion.appendChild(list);

    let gapnumber = 0;
    const nextLabel = () => {
        gapnumber += 1;
        return strings['player:gaplabel'].replace('%gap%', gapnumber);
    };

    await loadAllCues(list, exercise.totalcues, nextLabel);
    await restoreState(list);
    renderControls(player);

    if (mediaEl instanceof HTMLMediaElement) {
        attachSync(mediaEl, list, position, mediaregion.querySelector(SELECTORS.CAPTIONOVERLAY));
        attachPlaybackFlow(mediaEl, list, playback.effectivecuepausemode || 'auto');
    }

    if (overlaymode) {
        // The overlay only ever shows the cue that is playing, so the exercise
        // starts by putting the cursor where the work is. That also engages the
        // first cue, which is what makes playback stop at its end instead of
        // running the sentence off the screen.
        const firstopen = Array.from(list.querySelectorAll('.mod_elang-gapwrap[data-gapid] input'))
            .find((input) => input.value.trim() === '');
        if (firstopen) {
            firstopen.focus();
        }
    }

    const status = player.querySelector(SELECTORS.STATUS);
    if (status) {
        status.textContent = strings['player:ready'];
    }
};

/**
 * Initialise the player for a course module.
 *
 * @param {Number} cmid The course module id
 * @returns {void}
 */
export const init = (cmid) => {
    const player = document.querySelector(SELECTORS.PLAYER);
    if (!player) {
        return;
    }
    bootstrap(cmid, player).catch(async(error) => {
        Log.error(error);
        const status = player.querySelector(SELECTORS.STATUS);
        if (status) {
            status.textContent = await getString('player:loaderror', 'mod_elang');
        }
    });
};
