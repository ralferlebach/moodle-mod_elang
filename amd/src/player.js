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
 * saw so a lost-response retry is idempotent server-side. Media/cue
 * synchronisation and resume of prior input are added in later slices.
 *
 * @module     mod_elang/player
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString, getStrings} from 'core/str';
import Log from 'core/log';

const SELECTORS = {
    PLAYER: '[data-region="mod_elang/player"]',
    MEDIA: '[data-region="media"]',
    STATUS: '[data-region="status"]',
    TRANSCRIPT: '[data-region="transcript"]',
    CONTROLS: '[data-region="controls"]',
    SCORE: '[data-region="score"]',
};

const GAP_TOKEN = /\{\{gap:([^}]+)\}\}/g;

const PROVIDER_EMBEDS = {
    youtube: (ref) => `https://www.youtube-nocookie.com/embed/${encodeURIComponent(ref)}`,
    vimeo: (ref) => `https://player.vimeo.com/video/${encodeURIComponent(ref)}`,
};

const RESULT_STATES = {
    exact: {cls: 'mod_elang-correct', key: 'player:statecorrect'},
    wordrecognized: {cls: 'mod_elang-accepted', key: 'player:stateaccepted'},
    incorrect: {cls: 'mod_elang-incorrect', key: 'player:stateincorrect'},
    empty: {cls: 'mod_elang-empty', key: null},
};

const STATE_CLASSES = ['mod_elang-correct', 'mod_elang-accepted', 'mod_elang-incorrect', 'mod_elang-empty'];

// Module-level state for the single player on the page.
let attemptId = null;
const strings = {};

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
 * Render the attempt's medium into the media region.
 *
 * @param {Element} region The media region element
 * @param {Object} media The media descriptor from get_attempt_exercise
 * @returns {Element|null} The media element created, or null if none
 */
const renderMedia = (region, media) => {
    region.textContent = '';
    let element = null;
    if (media.kind === 'provider') {
        element = buildProviderEmbed(media);
    } else if (media.kind === 'file') {
        element = buildFileMedia(media);
    } else if (media.kind === 'url') {
        element = buildUrlMedia(media);
    }
    if (element) {
        region.appendChild(element);
    }
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
    state.textContent = parts.join(' — ');
};

/**
 * Submit a gap's current value, unless it is empty, and reflect the result.
 * Sends the tries count last seen so a lost-response retry is idempotent.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} input The gap input
 * @param {Element} state The gap's status element
 * @returns {Promise} Resolves once the result has been applied
 */
const submitGap = async(wrap, input, state) => {
    const value = input.value.trim();
    if (value === '' || wrap.dataset.submitting === '1') {
        return;
    }
    wrap.dataset.submitting = '1';
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
 * Build the hint button for a gap.
 *
 * @param {Element} wrap The gap wrapper
 * @param {Element} input The gap input
 * @param {Element} state The gap's status element
 * @returns {Element} The hint button
 */
const buildHintButton = (wrap, input, state) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'mod_elang-hintbtn btn btn-link btn-sm';
    button.textContent = strings['player:hint'];
    button.addEventListener('click', () => requestHint(wrap, input, state));
    return button;
};

/**
 * Build a gap: a wrapper holding the input, an aria-live status and a hint
 * button, wired to submit on Enter or on leaving the field.
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
            submitGap(wrap, input, state);
        }
    });
    input.addEventListener('blur', (event) => {
        // Leaving the field to click the hint button is not a submit.
        if (event.relatedTarget && event.relatedTarget.classList.contains('mod_elang-hintbtn')) {
            return;
        }
        submitGap(wrap, input, state);
    });

    wrap.appendChild(input);
    wrap.appendChild(state);
    wrap.appendChild(buildHintButton(wrap, input, state));
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
 * Keep the transcript in step with a native audio/video element: highlight the
 * cue covering the current playback time and let a click on a cue seek to it.
 * Provider embeds (cross-origin iframes) do not expose playback time, so they
 * are simply not synchronised.
 *
 * @param {HTMLMediaElement} mediaEl The audio or video element
 * @param {Element} list The transcript list element
 * @returns {void}
 */
const attachSync = (mediaEl, list) => {
    const items = Array.from(list.querySelectorAll('.mod_elang-cue'));
    let current = null;

    mediaEl.addEventListener('timeupdate', () => {
        const ms = mediaEl.currentTime * 1000;
        const active = items.find(
            (item) => ms >= parseFloat(item.dataset.starttime) && ms < parseFloat(item.dataset.endtime)
        ) || null;
        if (active === current) {
            return;
        }
        if (current) {
            current.classList.remove('mod_elang-current');
            current.removeAttribute('aria-current');
        }
        current = active;
        if (current) {
            current.classList.add('mod_elang-current');
            current.setAttribute('aria-current', 'true');
            current.scrollIntoView({block: 'nearest', behavior: 'smooth'});
        }
    });

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
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'mod_elang-finishbtn btn btn-primary';
    button.textContent = strings['player:finish'];
    button.addEventListener('click', () => {
        finishAttempt(player).catch((error) => {
            Log.error(error);
            const status = player.querySelector(SELECTORS.STATUS);
            if (status) {
                status.textContent = error.message || strings['player:submitfailed'];
            }
        });
    });
    controls.appendChild(button);
};

/**
 * Load and cache the player's UI strings.
 *
 * @returns {Promise} Resolves once strings are cached
 */
const loadStrings = async() => {
    const keys = [
        'player:gaplabel', 'player:hint', 'player:finish', 'player:finished',
        'player:statecorrect', 'player:stateaccepted', 'player:stateincorrect',
        'player:statehinted', 'player:submitfailed', 'player:scorelabel', 'player:ready',
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
    const mediaEl = renderMedia(player.querySelector(SELECTORS.MEDIA), exercise.media);

    const transcriptregion = player.querySelector(SELECTORS.TRANSCRIPT);
    transcriptregion.textContent = '';
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
        attachSync(mediaEl, list);
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
