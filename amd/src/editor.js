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
 * Editor bootstrap for mod_elang.
 *
 * This is the idiomatic Moodle (AMD/ES6) entry point. It is deliberately thin:
 * it resolves the language strings via core/str, builds an AJAX transport via
 * core/ajax, and then hands the separately bundled React editor a mount target
 * plus the injected dependencies.
 *
 * React itself cannot be built through Moodle's Grunt/AMD pipeline on 4.5-5.1
 * (core provides no React runtime there), so the React app is bundled
 * separately by build.mjs into js/vendor/react/editor.bundle.js and loaded by
 * edit.php as a page script exposing window.mod_elang_editor. From Moodle 5.2
 * onwards, where React ships in core, this can later be simplified to mount
 * through the core runtime instead of loading the standalone bundle.
 *
 * @module     mod_elang/editor
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_strings as getStrings} from 'core/str';
import {call as fetchMany} from 'core/ajax';
import Log from 'core/log';

/** @type {string[]} Editor string keys, kept in sync with lang/en/elang.php. */
const STRING_KEYS = [
    'editor_addcue', 'editor_addgap', 'editor_addhint', 'editor_addvariant', 'editor_advanced',
    'editor_linkurl', 'editor_linkurl_help', 'editor_maxlength', 'editor_maxlength_help',
    'editor_variantisregex', 'editor_variantmatching',
    'editor_algoexact', 'editor_algorithm', 'editor_algowordrecognized', 'editor_answers',
    'editor_autosaved', 'editor_autosaveerror',
    'editor_captureend', 'editor_capturestart', 'editor_cueactions', 'editor_cuecount',
    'editor_currentmedia', 'editor_deletecue',
    'editor_emptytranscript', 'editor_gapcount',
    'editor_insertafter', 'editor_insertbefore', 'editor_invalidtime',
    'editor_nocueselected', 'editor_nocuesmatch', 'editor_onlywarnings', 'editor_searchcues',
    'editor_warnemptysolution', 'editor_warnnotranscript', 'editor_warntiming',
    'editor_deletegap', 'editor_endtime', 'editor_gaprange', 'editor_hints', 'editor_hinttext',
    'editor_hinttype', 'editor_hinttype_firstletter', 'editor_hinttype_partial', 'editor_hinttype_solution',
    'editor_hinttype_text', 'editor_hinttype_translation', 'editor_hinttype_wordlength',
    'editor_formatsubrip', 'editor_formatwebvtt',
    'editor_import', 'editor_importappend', 'editor_importapply', 'editor_importcancel',
    'editor_importcheck', 'editor_importchecking', 'editor_importcuecount',
    'editor_importtoolarge', 'editor_importwrongtype',
    'editor_importduration', 'editor_importedcues', 'editor_importfilehint',
    'editor_importformat', 'editor_importfromfile', 'editor_importfromtext',
    'editor_importgapcount', 'editor_importhint', 'editor_importparseerror',
    'editor_importpastedtext', 'editor_importreaderror', 'editor_importready',
    'editor_importreplace', 'editor_importreplacedcues', 'editor_importsource',
    'editor_importsummary',
    'editor_loaderror', 'editor_loading', 'editor_media', 'editor_mediafile', 'editor_mediakind',
    'editor_medianone', 'editor_mediaprovider', 'editor_mediaproviderref', 'editor_mediaproviderrefhint',
    'editor_mediasaved',
    'editor_mediaurl', 'editor_nocues', 'editor_nogaps', 'editor_nomedia', 'editor_novideotrack',
    'editor_onboardinggaps', 'editor_onboardingimport', 'editor_onboardingintro', 'editor_onboardingmedia',
    'editor_onboardingtitle', 'editor_parsegaps',
    'editor_penalty', 'editor_poster', 'editor_preview', 'editor_publish', 'editor_published', 'editor_removehint',
    'editor_removevariant', 'editor_ruleapplied', 'editor_ruleapply', 'editor_ruleerror',
    'editor_ruleeverynth', 'editor_rulefound', 'editor_rulegenerate', 'editor_ruleinterval',
    'editor_ruletype', 'editor_rulewordlist', 'editor_rulewords',
    'editor_save', 'editor_saved', 'editor_saveerror',
    'editor_savemedia', 'editor_saving', 'editor_selecttext', 'editor_solution', 'editor_starttime',
    'editor_transcript', 'editor_unsaved', 'editor_uploadmedia', 'editor_waveform',
];

/**
 * Load all editor strings and return a key → text map.
 *
 * @returns {Promise<Object.<string, string>>} Resolved strings.
 */
const loadStrings = async() => {
    const requests = STRING_KEYS.map((key) => ({key, component: 'mod_elang'}));
    const values = await getStrings(requests);
    const map = {};
    STRING_KEYS.forEach((key, index) => {
        map[key] = values[index];
    });
    return map;
};

/**
 * Build a transport bound to Moodle's core/ajax, so the React app never needs
 * to know about Moodle's web service internals.
 *
 * @returns {function(string, Object): Promise<*>} A (methodname, args) transport.
 */
const buildTransport = () => (methodname, args) => {
    const [promise] = fetchMany([{methodname, args}]);
    return promise;
};

/**
 * Resolve the prebuilt React editor from the global the page script exposes.
 *
 * The React editor is bundled by build.mjs into js/vendor/react/
 * editor.bundle.js and loaded by edit.php as a regular page script (via
 * $PAGE->requires->js), which assigns its mount API to window.mod_elang_editor.
 * It lives outside amd/build/ on purpose: moodle-plugin-ci wipes amd/build/ and
 * re-runs Grunt, flagging any file Grunt cannot regenerate, and React cannot be
 * built by Moodle's Grunt pipeline. Loading through $PAGE->requires->js keeps
 * the script inside Moodle's asset handling; a short poll waits for the global
 * in case this AMD module initialises before the page script has executed.
 *
 * @returns {Promise<{mount: function(HTMLElement, Object): void}>} The editor API.
 */
const loadEditor = () => new Promise((resolve, reject) => {
    const deadline = Date.now() + 10000;
    const poll = () => {
        const editor = window.mod_elang_editor;
        if (editor && typeof editor.mount === 'function') {
            resolve(editor);
        } else if (Date.now() > deadline) {
            reject(new Error('eLang editor bundle did not load.'));
        } else {
            window.setTimeout(poll, 50);
        }
    };
    poll();
});

/**
 * Initialise the editor for a draft version.
 *
 * @param {Number} draftVersionId The draft elang_version id to edit
 * @returns {Promise} Resolves once the editor is mounted (or failed to)
 */
export const init = async(draftVersionId) => {
    const element = document.querySelector('[data-region="editorroot"]');
    if (!element) {
        return;
    }

    try {
        const [strings, editor] = await Promise.all([loadStrings(), loadEditor()]);
        editor.mount(element, {
            versionid: draftVersionId,
            mediauploadurl: element.dataset.mediauploadurl || '',
            callService: buildTransport(),
            getString: (key) => strings[key],
        });
    } catch (error) {
        Log.error(error);
        const status = element.querySelector('[data-region="status"]');
        if (status) {
            try {
                const [loaderror] = await getStrings([{key: 'editor_loaderror', component: 'mod_elang'}]);
                status.textContent = loaderror;
            } catch (stringerror) {
                Log.error(stringerror);
                status.textContent = 'The editor could not be loaded.';
            }
        }
    }
};
