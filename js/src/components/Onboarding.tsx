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
 * The empty-state guidance shown before an exercise has any cues.
 *
 * Rather than a bare "no cues yet" line, this walks a first-time author through
 * the studio workflow — choose a medium, import subtitles or add cues, mark the
 * gaps — and points at the next sensible step depending on whether a medium has
 * already been chosen.
 *
 * @module     mod_elang/components/Onboarding
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Translator} from '../types';

interface Props {
    t: Translator;
    /** Whether a medium has already been set, to point at the right next step. */
    hasmedia: boolean;
}

/**
 * Render the onboarding panel.
 *
 * @param props The component props.
 * @returns The onboarding element.
 */
export function Onboarding({t, hasmedia}: Props): JSX.Element {
    return (
        <div className="mod_elang-editor-onboarding alert alert-info" data-region="onboarding">
            <h4>{t('editor_onboardingtitle')}</h4>
            <p className="mb-1">{t('editor_onboardingintro')}</p>
            <ol className="mb-0">
                <li className={hasmedia ? 'text-muted' : ''}>{t('editor_onboardingmedia')}</li>
                <li>{t('editor_onboardingimport')}</li>
                <li>{t('editor_onboardinggaps')}</li>
            </ol>
        </div>
    );
}
