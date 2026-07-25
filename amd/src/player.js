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
 * Drives the learner-facing attempt lifecycle through the external API: it
 * starts or resumes the attempt, then renders the medium and the first page of
 * cues for the version the attempt is pinned to. The transcript is plain text
 * carrying {{gap:key}} tokens (solution-masked server-side); this module
 * replaces each token with a text input rather than injecting any markup, so
 * transcript text is only ever added as text nodes. Answering, media/cue
 * synchronisation and resume of prior input are added in later slices.
 *
 * @module     mod_elang/player
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString} from 'core/str';
import Log from 'core/log';

const SELECTORS = {
    PLAYER: '[data-region="mod_elang/player"]',
    MEDIA: '[data-region="media"]',
    STATUS: '[data-region="status"]',
    TRANSCRIPT: '[data-region="transcript"]',
};

const GAP_TOKEN = /\{\{gap:([^}]+)\}\}/g;

const PROVIDER_EMBEDS = {
    youtube: (ref) => `https://www.youtube-nocookie.com/embed/${encodeURIComponent(ref)}`,
    vimeo: (ref) => `https://player.vimeo.com/video/${encodeURIComponent(ref)}`,
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
 * Build an audio or video element and attach a poster and preload hint.
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
 * @returns {void}
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
};

/**
 * Build a text input for a gap.
 *
 * @param {Object|undefined} gap The gap record, if the token resolved to one
 * @param {String} label The accessible label for the input
 * @returns {Element} The input element
 */
const buildGapInput = (gap, label) => {
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'mod_elang-gap';
    input.setAttribute('autocomplete', 'off');
    input.setAttribute('aria-label', label);
    if (gap) {
        input.dataset.gapid = gap.id;
        if (gap.maxlength > 0) {
            input.maxLength = gap.maxlength;
        }
    }
    return input;
};

/**
 * Append a cue's transcript to a list item, replacing each {{gap:key}} token
 * with a gap input and everything else with text nodes.
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
        item.appendChild(buildGapInput(gapsByKey[match[1]], nextLabel()));
        lastindex = GAP_TOKEN.lastIndex;
    }
    const rest = transcript.slice(lastindex);
    if (rest) {
        item.appendChild(document.createTextNode(rest));
    }
};

/**
 * Render a page of cues as a transcript list with gap inputs in place.
 *
 * @param {Element} region The transcript region
 * @param {Array} cues The cues from get_attempt_cues
 * @returns {Promise} Resolves once the transcript is rendered
 */
const renderTranscript = async(region, cues) => {
    const labeltemplate = await getString('player:gaplabel', 'mod_elang', '%gap%');

    region.textContent = '';
    const list = document.createElement('ol');
    list.className = 'mod_elang-cues';

    let gapnumber = 0;
    const nextLabel = () => {
        gapnumber += 1;
        return labeltemplate.replace('%gap%', gapnumber);
    };

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

    region.appendChild(list);
};

/**
 * Start or resume the attempt and render its medium and first page of cues.
 *
 * @param {Number} cmid The course module id
 * @param {Element} player The player container
 * @returns {Promise} Resolves when the initial render is complete
 */
const bootstrap = async(cmid, player) => {
    const status = player.querySelector(SELECTORS.STATUS);
    const mediaregion = player.querySelector(SELECTORS.MEDIA);
    const transcriptregion = player.querySelector(SELECTORS.TRANSCRIPT);

    const attempt = await callWs('mod_elang_start_attempt', {cmid});
    const exercise = await callWs('mod_elang_get_attempt_exercise', {attemptid: attempt.attemptid});
    renderMedia(mediaregion, exercise.media);

    const page = await callWs('mod_elang_get_attempt_cues', {
        attemptid: attempt.attemptid,
        offset: 0,
        limit: 50,
    });
    await renderTranscript(transcriptregion, page.cues);

    if (status) {
        status.textContent = await getString('player:ready', 'mod_elang');
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
