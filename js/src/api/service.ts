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
 * Typed client for the mod_elang authoring external functions.
 *
 * The transport is injected so the same client works whether it is driven by
 * Moodle's core/ajax (production) or an in-memory stub (tests). Payloads sent
 * to save_draft_version are rebuilt field by field: Moodle's parameter
 * validation rejects unexpected keys, so the client guarantees only the
 * declared structure ever crosses the wire.
 *
 * @module     mod_elang/api/service
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {Cue, ImportResult, Media, ServiceTransport, VersionContent} from '../types';

/**
 * Reduce a cue list to exactly the fields save_draft_version declares,
 * re-sequencing cue and gap sort orders as it goes.
 *
 * @param cues The editor's cue model.
 * @returns The wire-shaped cue list.
 */
export function toSavePayload(cues: Cue[]): Cue[] {
    return cues.map((cue, cueindex) => ({
        cuekey: cue.cuekey,
        sortorder: cueindex + 1,
        starttime: cue.starttime,
        endtime: cue.endtime,
        transcript: cue.transcript,
        transcriptformat: cue.transcriptformat,
        gaps: cue.gaps.map((gap, gapindex) => ({
            gapkey: gap.gapkey,
            sortorder: gapindex + 1,
            charstart: gap.charstart,
            charlength: gap.charlength,
            solution: gap.solution,
            gradingalgorithm: gap.gradingalgorithm,
            maxlength: gap.maxlength,
            linkurl: gap.linkurl,
            answers: gap.answers.map((answer, answerindex) => ({
                sortorder: answerindex + 1,
                answer: answer.answer,
                isregex: answer.isregex,
            })),
            hints: gap.hints.map((hint, hintindex) => ({
                level: hintindex + 1,
                hinttype: hint.hinttype,
                hinttext: hint.hinttext,
                penalty: hint.penalty,
            })),
        })),
    }));
}

export class ApiClient {
    private readonly transport: ServiceTransport;
    private readonly versionid: number;

    /**
     * @param transport The (methodname, args) transport.
     * @param versionid The draft version this client edits.
     */
    constructor(transport: ServiceTransport, versionid: number) {
        this.transport = transport;
        this.versionid = versionid;
    }

    /**
     * Load the draft's full content, including solutions and media URLs.
     *
     * @returns The version content.
     */
    getVersionContent(): Promise<VersionContent> {
        return this.transport('mod_elang_get_version_content', {
            versionid: this.versionid,
        }) as Promise<VersionContent>;
    }

    /**
     * Persist the draft, sending the last seen revision as an
     * optimistic-concurrency token.
     *
     * @param expectedrevision The revision the editor last saw.
     * @param cues The full cue list to store.
     * @returns The new revision.
     */
    async saveDraft(expectedrevision: number, cues: Cue[]): Promise<number> {
        const result = await this.transport('mod_elang_save_draft_version', {
            versionid: this.versionid,
            expectedrevision,
            cues: toSavePayload(cues),
        }) as {revision: number};
        return result.revision;
    }

    /**
     * Validate and publish the draft.
     *
     * @returns Resolves once published.
     */
    async publish(): Promise<void> {
        await this.transport('mod_elang_publish_version', {versionid: this.versionid});
    }

    /**
     * Parse pasted subtitle text into cue previews.
     *
     * @param subtitles The raw WebVTT or SubRip content.
     * @param parsegaps Whether to recognise V1 inline gap markers.
     * @returns The parsed cues and warnings.
     */
    previewImport(subtitles: string, parsegaps: boolean): Promise<ImportResult> {
        return this.transport('mod_elang_preview_import', {
            versionid: this.versionid,
            subtitles,
            parsegaps,
        }) as Promise<ImportResult>;
    }

    /**
     * Set the draft's medium from the media panel.
     *
     * @param kind The medium kind: url, provider or empty for none.
     * @param url The direct media URL when kind is url.
     * @param provider The provider name when kind is provider.
     * @param providerref The provider-specific reference.
     * @returns The stored media descriptor.
     */
    setMedia(kind: string, url: string, provider: string, providerref: string): Promise<Media> {
        return this.transport('mod_elang_set_draft_media', {
            versionid: this.versionid,
            kind,
            url,
            provider,
            providerref,
        }) as Promise<Media>;
    }
}
