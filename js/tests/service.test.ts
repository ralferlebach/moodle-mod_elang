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
 * Tests for the typed web-service client: exact wire payloads (Moodle's
 * parameter validation rejects unexpected keys) and sort-order re-sequencing.
 *
 * @module     mod_elang/tests/service
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {ApiClient, toSavePayload} from '../src/api/service';
import {Cue, ServiceTransport} from '../src/types';

function cueFixture(): Cue {
    return {
        cuekey: 'c1',
        sortorder: 99,
        starttime: 1000,
        endtime: 2000,
        transcript: 'Der Hund läuft',
        transcriptformat: 2,
        gaps: [{
            gapkey: 'g1',
            sortorder: 42,
            charstart: 4,
            charlength: 4,
            solution: 'Hund',
            gradingalgorithm: 'exact',
            maxlength: 0,
            linkurl: '',
            answers: [{sortorder: 7, answer: 'Hunde', isregex: 0}],
            hints: [{level: 5, hinttype: 'solution', hinttext: 'Hund', penalty: 0}],
        }],
    };
}

describe('toSavePayload', () => {
    test('re-sequences cue, gap, answer and hint orders from 1', () => {
        const payload = toSavePayload([cueFixture()]);

        expect(payload[0].sortorder).toBe(1);
        expect(payload[0].gaps[0].sortorder).toBe(1);
        expect(payload[0].gaps[0].answers[0].sortorder).toBe(1);
        expect(payload[0].gaps[0].hints[0].level).toBe(1);
    });

    test('emits exactly the declared wire fields', () => {
        const stale = cueFixture() as Cue & {clientonly?: string};
        stale.clientonly = 'must not cross the wire';

        const payload = toSavePayload([stale]) as unknown as Array<Record<string, unknown>>;

        expect(Object.keys(payload[0]).sort()).toEqual(
            ['cuekey', 'endtime', 'gaps', 'sortorder', 'starttime', 'transcript', 'transcriptformat']
        );
    });
});

describe('ApiClient', () => {
    function recordingTransport(): {transport: ServiceTransport; calls: Array<{method: string; args: Record<string, unknown>}>} {
        const calls: Array<{method: string; args: Record<string, unknown>}> = [];
        const transport: ServiceTransport = async(method, args) => {
            calls.push({method, args});
            if (method === 'mod_elang_save_draft_version') {
                return {versionid: 7, revision: 3};
            }
            if (method === 'mod_elang_generate_rule_gaps') {
                return {gaps: [{charstart: 3, charlength: 4, solution: 'chat'}]};
            }
            return {};
        };
        return {transport, calls};
    }

    test('saveDraft sends the revision token and adopts the returned revision', async() => {
        const {transport, calls} = recordingTransport();
        const api = new ApiClient(transport, 7);

        const revision = await api.saveDraft(2, [cueFixture()]);

        expect(revision).toBe(3);
        expect(calls[0].method).toBe('mod_elang_save_draft_version');
        expect(calls[0].args.versionid).toBe(7);
        expect(calls[0].args.expectedrevision).toBe(2);
    });

    test('previewImport forwards the parsegaps option', async() => {
        const {transport, calls} = recordingTransport();
        const api = new ApiClient(transport, 7);

        await api.previewImport('WEBVTT', true);

        expect(calls[0].method).toBe('mod_elang_preview_import');
        expect(calls[0].args).toEqual({versionid: 7, subtitles: 'WEBVTT', parsegaps: true});
    });

    test('generateRuleGaps forwards the rule and returns the gaps', async() => {
        const {transport, calls} = recordingTransport();
        const api = new ApiClient(transport, 7);

        const gaps = await api.generateRuleGaps('Le chat dort', {type: 'words', words: ['chat']});

        expect(gaps).toEqual([{charstart: 3, charlength: 4, solution: 'chat'}]);
        expect(calls[0].method).toBe('mod_elang_generate_rule_gaps');
        expect(calls[0].args.versionid).toBe(7);
        expect(calls[0].args.transcript).toBe('Le chat dort');
        expect(calls[0].args.rule).toEqual({
            type: 'words',
            words: ['chat'],
            n: 1,
            offset: 0,
            casesensitive: false,
        });
    });
});
