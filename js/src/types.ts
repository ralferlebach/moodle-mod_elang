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
 * Shared editor types, mirroring the mod_elang authoring web-service
 * structures (authoring_helper::cue_structure and friends). The editor sends
 * exactly these shapes back through save_draft_version, so any field added
 * here must exist in the external structures too.
 *
 * @module     mod_elang/types
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Moodle's FORMAT_PLAIN text format constant, used for cues the editor creates. */
export const FORMAT_PLAIN = 2;

export interface Answer {
    sortorder: number;
    answer: string;
    isregex: number;
}

export interface Hint {
    level: number;
    hinttype: string;
    hinttext: string;
    penalty: number;
}

export interface Gap {
    gapkey: string;
    sortorder: number;
    charstart: number;
    charlength: number;
    solution: string;
    gradingalgorithm: string;
    maxlength: number;
    linkurl: string;
    answers: Answer[];
    hints: Hint[];
}

export interface Cue {
    cuekey: string;
    sortorder: number;
    starttime: number;
    endtime: number;
    transcript: string;
    transcriptformat: number;
    gaps: Gap[];
}

/** A gap recognised from V1 inline markers by preview_import's parsegaps option. */
export interface ImportedGap {
    charstart: number;
    charlength: number;
    solution: string;
    hintsallowed: boolean;
}

export interface ImportedCue {
    sortorder: number;
    starttime: number;
    endtime: number;
    transcript: string;
    transcriptformat: number;
    gaps: ImportedGap[];
}

export interface ImportResult {
    cues: ImportedCue[];
    cuecount: number;
    warnings: string[];
}

/** The media descriptor shared by get_version_content and set_draft_media. */
export interface Media {
    mediakind: string;
    mediaurl: string;
    mediaprovider: string;
    mediaproviderref: string;
    mediafilename: string;
    mediafileurl: string;
}

/** One curated media provider the player can embed. */
export interface ProviderOption {
    key: string;
    name: string;
}

export interface VersionContent extends Media {
    versionid: number;
    revision: number;
    cues: Cue[];
    mediaproviders?: ProviderOption[];
}

/** A (methodname, args) transport, normally bound to Moodle's core/ajax. */
export type ServiceTransport = (methodname: string, args: Record<string, unknown>) => Promise<unknown>;

/** A string resolver for the editor's UI texts. */
export type Translator = (key: string) => string;

export interface MountConfig {
    versionid: number;
    mediauploadurl?: string;
    callService?: ServiceTransport;
    getString?: Translator;
}
