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
    'editor:addcue', 'editor:addgap', 'editor:addhint', 'editor:addvariant',
    'editor:algoexact', 'editor:algorithm', 'editor:algowordrecognized', 'editor:answers',
    'editor:autosaved', 'editor:autosaveerror',
    'editor:captureend', 'editor:capturestart', 'editor:currentmedia', 'editor:deletecue',
    'editor:deletegap', 'editor:endtime', 'editor:gaprange', 'editor:hints', 'editor:hinttext',
    'editor:hinttype', 'editor:hinttype_firstletter', 'editor:hinttype_partial', 'editor:hinttype_solution',
    'editor:hinttype_text', 'editor:hinttype_translation', 'editor:hinttype_wordlength',
    'editor:formatsubrip', 'editor:formatwebvtt',
    'editor:import', 'editor:importappend', 'editor:importapply', 'editor:importcancel',
    'editor:importcheck', 'editor:importchecking', 'editor:importcuecount',
    'editor:importduration', 'editor:importedcues', 'editor:importfilehint',
    'editor:importformat', 'editor:importfromfile', 'editor:importfromtext',
    'editor:importgapcount', 'editor:importhint', 'editor:importparseerror',
    'editor:importpastedtext', 'editor:importreaderror', 'editor:importready',
    'editor:importreplace', 'editor:importreplacedcues', 'editor:importsource',
    'editor:importsummary',
    'editor:loaderror', 'editor:loading', 'editor:media', 'editor:mediafile', 'editor:mediakind',
    'editor:medianone', 'editor:mediaprovider', 'editor:mediaproviderref', 'editor:mediaproviderrefhint',
    'editor:mediasaved',
    'editor:mediaurl', 'editor:nocues', 'editor:nogaps', 'editor:nomedia', 'editor:novideotrack',
    'editor:onboardinggaps', 'editor:onboardingimport', 'editor:onboardingintro', 'editor:onboardingmedia',
    'editor:onboardingtitle', 'editor:parsegaps',
    'editor:penalty', 'editor:poster', 'editor:preview', 'editor:publish', 'editor:published', 'editor:removehint',
    'editor:removevariant', 'editor:ruleapplied', 'editor:ruleapply', 'editor:ruleerror',
    'editor:ruleeverynth', 'editor:rulefound', 'editor:rulegenerate', 'editor:ruleinterval',
    'editor:ruletype', 'editor:rulewordlist', 'editor:rulewords',
    'editor:save', 'editor:saved', 'editor:saveerror',
    'editor:savemedia', 'editor:saving', 'editor:selecttext', 'editor:solution', 'editor:starttime',
    'editor:transcript', 'editor:unsaved', 'editor:uploadmedia', 'editor:waveform',
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
                const [loaderror] = await getStrings([{key: 'editor:loaderror', component: 'mod_elang'}]);
                status.textContent = loaderror;
            } catch (stringerror) {
                Log.error(stringerror);
                status.textContent = 'The editor could not be loaded.';
            }
        }
    }
};
