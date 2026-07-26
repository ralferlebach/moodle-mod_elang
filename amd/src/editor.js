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
 * Exercise content editor.
 *
 * Loads the draft version through the external API, renders its cues as an
 * editable list, and drives subtitle import, saving and publishing. This is the
 * editor foundation: cue timings and transcripts can be edited, cues added,
 * removed and imported from WebVTT/SubRip, and the draft saved (with its
 * revision as an optimistic-concurrency token) or validated and published.
 * Existing gaps are preserved and round-tripped untouched; gap authoring and
 * the media panel are layered on in later slices.
 *
 * @module     mod_elang/editor
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {getString, getStrings} from 'core/str';
import Log from 'core/log';

const SELECTORS = {
    EDITOR: '[data-region="mod_elang/editor"]',
    STATUS: '[data-region="status"]',
    CUES: '[data-region="cues"]',
    IMPORTTEXT: '[data-region="importtext"]',
    SAVE: '[data-action="save"]',
    PUBLISH: '[data-action="publish"]',
    ADDCUE: '[data-action="addcue"]',
    IMPORT: '[data-action="import"]',
    CURRENTMEDIA: '[data-region="currentmedia"]',
    MEDIAKIND: '[data-region="mediakind"]',
    MEDIAURLFIELD: '[data-region="mediaurlfield"]',
    MEDIAURLINPUT: '[data-region="mediaurlinput"]',
    MEDIAPROVIDERFIELDS: '[data-region="mediaproviderfields"]',
    MEDIAPROVIDERINPUT: '[data-region="mediaproviderinput"]',
    MEDIAPROVIDERREFINPUT: '[data-region="mediaproviderrefinput"]',
    SAVEMEDIA: '[data-action="savemedia"]',
};

// Moodle FORMAT_PLAIN, used for cues the editor creates.
const FORMAT_PLAIN = 2;

// Module-level state for the single editor on the page.
let versionid = null;
const state = {revision: 0, cues: []};
const strings = {};

/**
 * Call an external function and return its promise.
 *
 * @param {String} methodname The external function name
 * @param {Object} args The call arguments
 * @returns {Promise} The call's promise
 */
const callWs = (methodname, args) => Ajax.call([{methodname, args}])[0];

/**
 * Write a message into the status region.
 *
 * @param {String} text The message to show
 * @returns {void}
 */
const setStatus = (text) => {
    const region = document.querySelector(SELECTORS.STATUS);
    if (region) {
        region.textContent = text;
    }
};

/**
 * Generate a version-stable key for a new cue or gap: an alphanumeric string
 * (matching PARAM_ALPHANUMEXT) of at most 40 characters.
 *
 * @param {String} prefix A short prefix identifying the kind of key
 * @returns {String} The generated key
 */
const newKey = (prefix) => {
    const random = Math.random().toString(36).slice(2, 10);
    return (prefix + Date.now().toString(36) + random).slice(0, 40);
};

/**
 * Load and cache the editor's dynamic UI strings.
 *
 * @returns {Promise} Resolves once the strings are cached
 */
const loadStrings = async() => {
    const keys = [
        'editor:transcript', 'editor:starttime', 'editor:endtime', 'editor:gaps', 'editor:nogaps',
        'editor:nocues', 'editor:deletecue', 'editor:loaderror', 'editor:saved', 'editor:saveerror',
        'editor:published', 'editor:currentmedia', 'editor:nomedia', 'editor:mediafile', 'editor:mediasaved',
    ];
    const values = await getStrings(keys.map((key) => ({key, component: 'mod_elang'})));
    keys.forEach((key, index) => {
        strings[key] = values[index];
    });
};

/**
 * Build a labelled number field wired to update the model on input.
 *
 * @param {String} labeltext The field label
 * @param {Number} value The initial value
 * @param {Function} onchange Called with the parsed integer whenever it changes
 * @returns {Element} The field wrapper
 */
const buildNumberField = (labeltext, value, onchange) => {
    const wrap = document.createElement('span');
    wrap.className = 'mr-3';

    const label = document.createElement('label');
    label.className = 'mr-1';
    label.textContent = labeltext;

    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'form-control';
    input.min = '0';
    input.value = String(value);
    input.addEventListener('input', () => {
        onchange(parseInt(input.value, 10) || 0);
    });

    label.appendChild(input);
    wrap.appendChild(label);
    return wrap;
};

/**
 * Summarise a cue's gaps as a short read-only line.
 *
 * @param {Array} gaps The cue's gaps
 * @returns {String} A human-readable summary
 */
const summariseGaps = (gaps) => {
    if (!gaps || gaps.length === 0) {
        return strings['editor:nogaps'];
    }
    return strings['editor:gaps'] + ': ' + gaps.map((gap) => gap.solution).join(', ');
};

/**
 * Build the editable row for one cue, wired to mutate the cue in place.
 *
 * @param {Object} cue The cue model object
 * @returns {Element} The cue row element
 */
const buildCueRow = (cue) => {
    const row = document.createElement('div');
    row.className = 'mod_elang-editor-cue card mb-2';

    const body = document.createElement('div');
    body.className = 'card-body';

    const timing = document.createElement('div');
    timing.className = 'mb-2';
    timing.appendChild(buildNumberField(strings['editor:starttime'], cue.starttime, (value) => {
        cue.starttime = value;
    }));
    timing.appendChild(buildNumberField(strings['editor:endtime'], cue.endtime, (value) => {
        cue.endtime = value;
    }));

    const label = document.createElement('label');
    label.className = 'd-block';
    label.textContent = strings['editor:transcript'];

    const textarea = document.createElement('textarea');
    textarea.className = 'form-control';
    textarea.rows = 2;
    textarea.value = cue.transcript;
    textarea.addEventListener('input', () => {
        cue.transcript = textarea.value;
    });
    label.appendChild(textarea);

    const gaps = document.createElement('p');
    gaps.className = 'text-muted mt-2 mb-1';
    gaps.textContent = summariseGaps(cue.gaps);

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'btn btn-link text-danger p-0';
    remove.textContent = strings['editor:deletecue'];
    remove.addEventListener('click', () => {
        state.cues = state.cues.filter((candidate) => candidate !== cue);
        renderCues();
    });

    body.appendChild(timing);
    body.appendChild(label);
    body.appendChild(gaps);
    body.appendChild(remove);
    row.appendChild(body);
    return row;
};

/**
 * Render the cue list from the current model, replacing whatever was there.
 *
 * @returns {void}
 */
const renderCues = () => {
    const container = document.querySelector(SELECTORS.CUES);
    if (!container) {
        return;
    }
    while (container.firstChild) {
        container.removeChild(container.firstChild);
    }

    if (state.cues.length === 0) {
        const empty = document.createElement('p');
        empty.className = 'text-muted';
        empty.textContent = strings['editor:nocues'];
        container.appendChild(empty);
        return;
    }

    state.cues.forEach((cue) => container.appendChild(buildCueRow(cue)));
};

/**
 * Persist the current draft, sending the last seen revision as an
 * optimistic-concurrency token and adopting the revision the save returns.
 *
 * @returns {Promise} Resolves once the draft is saved
 */
const saveDraft = async() => {
    state.cues.forEach((cue, index) => {
        cue.sortorder = index + 1;
    });
    const result = await callWs('mod_elang_save_draft_version', {
        versionid: versionid,
        expectedrevision: state.revision,
        cues: state.cues,
    });
    state.revision = result.revision;
};

/**
 * Save the draft in response to the toolbar button.
 *
 * @returns {Promise} Resolves once the save attempt has settled
 */
const handleSave = async() => {
    try {
        await saveDraft();
        setStatus(strings['editor:saved']);
    } catch (error) {
        Log.error(error);
        setStatus(error.message || strings['editor:saveerror']);
    }
};

/**
 * Save then validate-and-publish the draft, reloading afterwards so a fresh
 * draft is branched for further editing.
 *
 * @returns {Promise} Resolves once the publish attempt has settled
 */
const handlePublish = async() => {
    try {
        await saveDraft();
        await callWs('mod_elang_publish_version', {versionid: versionid});
        setStatus(strings['editor:published']);
        window.setTimeout(() => window.location.reload(), 1200);
    } catch (error) {
        Log.error(error);
        setStatus(error.message || strings['editor:saveerror']);
    }
};

/**
 * Append a fresh empty cue and re-render.
 *
 * @returns {void}
 */
const handleAddCue = () => {
    state.cues.push({
        cuekey: newKey('c'),
        sortorder: state.cues.length + 1,
        starttime: 0,
        endtime: 0,
        transcript: '',
        transcriptformat: FORMAT_PLAIN,
        gaps: [],
    });
    renderCues();
};

/**
 * Parse the pasted subtitle text through the importer and append the resulting
 * cues to the draft.
 *
 * @returns {Promise} Resolves once the import attempt has settled
 */
const handleImport = async() => {
    const textarea = document.querySelector(SELECTORS.IMPORTTEXT);
    if (!textarea || textarea.value.trim() === '') {
        return;
    }
    try {
        const result = await callWs('mod_elang_preview_import', {
            versionid: versionid,
            subtitles: textarea.value,
        });
        result.cues.forEach((cue) => {
            state.cues.push({
                cuekey: newKey('c'),
                sortorder: state.cues.length + 1,
                starttime: cue.starttime,
                endtime: cue.endtime,
                transcript: cue.transcript,
                transcriptformat: cue.transcriptformat,
                gaps: [],
            });
        });
        textarea.value = '';
        renderCues();
        setStatus(await getString('editor:importedcues', 'mod_elang', result.cuecount));
    } catch (error) {
        Log.error(error);
        setStatus(error.message || strings['editor:saveerror']);
    }
};

/**
 * Render the current-medium line, with a link when there is a file or url.
 *
 * @param {Object} media A media descriptor (mediakind, mediaurl, media file fields, ...)
 * @returns {void}
 */
const renderCurrentMedia = (media) => {
    const region = document.querySelector(SELECTORS.CURRENTMEDIA);
    if (!region) {
        return;
    }
    while (region.firstChild) {
        region.removeChild(region.firstChild);
    }

    const prefix = document.createElement('span');
    prefix.textContent = strings['editor:currentmedia'] + ' ';
    region.appendChild(prefix);

    if (media.mediakind === 'file' && media.mediafileurl) {
        const link = document.createElement('a');
        link.href = media.mediafileurl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = strings['editor:mediafile'] + ' (' + media.mediafilename + ')';
        region.appendChild(link);
    } else if (media.mediakind === 'url' && media.mediaurl) {
        const link = document.createElement('a');
        link.href = media.mediaurl;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = media.mediaurl;
        region.appendChild(link);
    } else if (media.mediakind === 'provider') {
        const text = document.createElement('span');
        text.textContent = media.mediaprovider + ' (' + media.mediaproviderref + ')';
        region.appendChild(text);
    } else {
        const text = document.createElement('span');
        text.textContent = strings['editor:nomedia'];
        region.appendChild(text);
    }
};

/**
 * Show only the media fields relevant to the selected medium type.
 *
 * @returns {void}
 */
const toggleMediaFields = () => {
    const select = document.querySelector(SELECTORS.MEDIAKIND);
    if (!select) {
        return;
    }
    const urlfield = document.querySelector(SELECTORS.MEDIAURLFIELD);
    if (urlfield) {
        urlfield.style.display = select.value === 'url' ? '' : 'none';
    }
    const providerfields = document.querySelector(SELECTORS.MEDIAPROVIDERFIELDS);
    if (providerfields) {
        providerfields.style.display = select.value === 'provider' ? '' : 'none';
    }
};

/**
 * Fill the media panel from a loaded version's descriptor. A file medium is
 * shown in the current-medium line but not offered in the type selector, which
 * only sets url, provider or none until the upload panel is added.
 *
 * @param {Object} content The version content returned by get_version_content
 * @returns {void}
 */
const populateMedia = (content) => {
    const select = document.querySelector(SELECTORS.MEDIAKIND);
    if (select) {
        select.value = (content.mediakind === 'url' || content.mediakind === 'provider') ? content.mediakind : '';
    }
    const urlinput = document.querySelector(SELECTORS.MEDIAURLINPUT);
    if (urlinput) {
        urlinput.value = content.mediaurl || '';
    }
    const providerinput = document.querySelector(SELECTORS.MEDIAPROVIDERINPUT);
    if (providerinput) {
        providerinput.value = content.mediaprovider || '';
    }
    const providerrefinput = document.querySelector(SELECTORS.MEDIAPROVIDERREFINPUT);
    if (providerrefinput) {
        providerrefinput.value = content.mediaproviderref || '';
    }
    renderCurrentMedia(content);
    toggleMediaFields();
};

/**
 * Set the draft's medium from the media panel.
 *
 * @returns {Promise} Resolves once the save attempt has settled
 */
const handleSaveMedia = async() => {
    const select = document.querySelector(SELECTORS.MEDIAKIND);
    if (!select) {
        return;
    }
    const urlinput = document.querySelector(SELECTORS.MEDIAURLINPUT);
    const providerinput = document.querySelector(SELECTORS.MEDIAPROVIDERINPUT);
    const providerrefinput = document.querySelector(SELECTORS.MEDIAPROVIDERREFINPUT);
    try {
        const media = await callWs('mod_elang_set_draft_media', {
            versionid: versionid,
            kind: select.value,
            url: urlinput ? urlinput.value : '',
            provider: providerinput ? providerinput.value : '',
            providerref: providerrefinput ? providerrefinput.value : '',
        });
        renderCurrentMedia(media);
        setStatus(strings['editor:mediasaved']);
    } catch (error) {
        Log.error(error);
        setStatus(error.message || strings['editor:saveerror']);
    }
};

/**
 * Wire the toolbar buttons to their handlers.
 *
 * @param {Element} editor The editor root element
 * @returns {void}
 */
const wireToolbar = (editor) => {
    const save = editor.querySelector(SELECTORS.SAVE);
    if (save) {
        save.addEventListener('click', () => {
            handleSave();
        });
    }
    const publish = editor.querySelector(SELECTORS.PUBLISH);
    if (publish) {
        publish.addEventListener('click', () => {
            handlePublish();
        });
    }
    const addcue = editor.querySelector(SELECTORS.ADDCUE);
    if (addcue) {
        addcue.addEventListener('click', () => {
            handleAddCue();
        });
    }
    const importbtn = editor.querySelector(SELECTORS.IMPORT);
    if (importbtn) {
        importbtn.addEventListener('click', () => {
            handleImport();
        });
    }
    const mediakind = editor.querySelector(SELECTORS.MEDIAKIND);
    if (mediakind) {
        mediakind.addEventListener('change', () => {
            toggleMediaFields();
        });
    }
    const savemedia = editor.querySelector(SELECTORS.SAVEMEDIA);
    if (savemedia) {
        savemedia.addEventListener('click', () => {
            handleSaveMedia();
        });
    }
};

/**
 * Initialise the editor for a draft version.
 *
 * @param {Number} draftVersionId The draft elang_version id to edit
 * @returns {Promise} Resolves once the draft has loaded (or failed to)
 */
export const init = async(draftVersionId) => {
    versionid = draftVersionId;

    const editor = document.querySelector(SELECTORS.EDITOR);
    if (!editor) {
        return;
    }

    try {
        await loadStrings();
    } catch (error) {
        Log.error(error);
    }

    wireToolbar(editor);

    try {
        const content = await callWs('mod_elang_get_version_content', {versionid: versionid});
        state.revision = content.revision;
        state.cues = content.cues;
        renderCues();
        populateMedia(content);
        setStatus('');
    } catch (error) {
        Log.error(error);
        setStatus(strings['editor:loaderror'] || '');
    }
};
