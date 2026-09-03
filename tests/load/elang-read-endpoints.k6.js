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

//
// k6 read-endpoint load test for mod_elang.
//
// Drives the REST web service `mod_elang_get_version_content` — the batched
// content-assembly read that the N+1 work made scale flat with cue/gap count —
// under a ramping virtual-user load, and fails the run if too many requests
// error or the p95 latency regresses past the threshold.
//
// Run against a disposable dev/staging site only. Seed a large exercise and mint
// a token first (make load-seed), then:
//   k6 run elang-read-endpoints.k6.js -e BASE_URL=<wwwroot> -e TOKEN=<t> \
//       -e CMID=<id> -e VERSIONID=<id>
//
// @package    mod_elang
// @copyright  2026 Ralf Erlebach
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
//

import http from 'k6/http';
import {check, fail} from 'k6';
import {Rate, Trend} from 'k6/metrics';

const BASE_URL = __ENV.BASE_URL;
const TOKEN = __ENV.TOKEN;
const VERSIONID = __ENV.VERSIONID;

// A dedicated error rate and latency trend for the content read, so thresholds
// judge this endpoint specifically rather than every request k6 makes.
const contentErrors = new Rate('elang_content_errors');
const contentLatency = new Trend('elang_content_latency', true);

// The share of reads that met the target. A **metric**, deliberately not a
// threshold: k6 has no notion of a threshold that only reports — any crossed
// threshold sets exit code 99, and abortOnFail only decides whether the run
// stops early. Expressing "should feel like this" as a threshold therefore
// turned every run that was perfectly acceptable into a failed one.
const contentWithinTarget = new Rate('elang_content_within_target');

export const options = {
    scenarios: {
        read_content: {
            executor: 'ramping-vus',
            startVUs: 1,
            // The ramp scales with the load. Fifteen seconds is right for a
            // classroom and wrong for a lecture hall: arriving at 2000 virtual
            // users that fast measures the ramp rather than the plateau, and
            // the first seconds of a cold connection pool dominate the p95.
            stages: [
                {duration: __ENV.RAMPUP || (Number(__ENV.VUS || 25) > 500 ? '60s' : '15s'),
                    target: Number(__ENV.VUS || 25)},
                {duration: __ENV.DURATION || '1m', target: Number(__ENV.VUS || 25)},
                {duration: '10s', target: 0},
            ],
            gracefulRampDown: '10s',
        },
    },
    thresholds: {
        // Exactly one latency threshold, because a threshold is a gate and a
        // gate has one answer.
        //
        //   p95 < 800 ms  fails the run. Above this a learner typing an answer
        //                 waits long enough to wonder whether the key
        //                 registered, and every answer in this exercise is a
        //                 request.
        //
        // The 300 ms the exercise *should* feel like is reported as
        // elang_content_within_target — the share of reads that met it — and
        // as a line in the summary. It is a trend to watch, not a gate: a run
        // at 400 ms is worth knowing about and is not a failure.
        elang_content_errors: ['rate<0.01'],
        elang_content_latency: ['p(95)<' + Number(__ENV.P95 || 800)],
        http_req_failed: ['rate<0.01'],
    },
};

export function setup() {
    if (!BASE_URL || !TOKEN || !VERSIONID) {
        fail('BASE_URL, TOKEN and VERSIONID must be provided via -e (see make load-seed).');
    }
    return {
        url: BASE_URL.replace(/\/$/, '') + '/webservice/rest/server.php',
    };
}

export default function (data) {
    const params = {
        wstoken: TOKEN,
        wsfunction: 'mod_elang_get_version_content',
        moodlewsrestformat: 'json',
        versionid: VERSIONID,
    };

    const res = http.get(data.url + '?' + toQuery(params), {
        tags: {name: 'get_version_content'},
    });
    contentLatency.add(res.timings.duration);
    contentWithinTarget.add(res.timings.duration < Number(__ENV.P95_TARGET || 300));

    // A Moodle web-service error still returns HTTP 200 with an "exception"
    // field, so a valid content read is 200 *and* carries a cues array.
    let ok = res.status === 200;
    if (ok) {
        try {
            const body = JSON.parse(res.body);
            ok = !body.exception && Array.isArray(body.cues);
        } catch (e) {
            ok = false;
        }
    }
    contentErrors.add(!ok);
    check(res, {'content read ok': () => ok});
}

/**
 * Build a URL query string from a params object.
 *
 * @param {object} params Key/value pairs to encode.
 * @returns {string} The encoded query string.
 */
function toQuery(params) {
    return Object.keys(params)
        .map((k) => encodeURIComponent(k) + '=' + encodeURIComponent(params[k]))
        .join('&');
}

/**
 * Print the verdict alongside k6's own summary.
 *
 * Three numbers — p95, limit, target — are enough to work it out and easy to
 * misread, especially in a CI log skimmed after a red build. The gate and the
 * aspiration are different things and are said to be different things here.
 *
 * @param {Object} data The end-of-test summary k6 assembles
 * @returns {Object} What to write where
 */
export function handleSummary(data) {
    const limit = Number(__ENV.P95 || 800);
    const target = Number(__ENV.P95_TARGET || 300);
    const p95 = data.metrics.elang_content_latency
        ? data.metrics.elang_content_latency.values['p(95)']
        : null;
    const within = data.metrics.elang_content_within_target
        ? data.metrics.elang_content_within_target.values.rate * 100
        : null;

    const lines = ['', '=== mod_elang Lastergebnis ==='];
    if (p95 === null) {
        lines.push('Keine Messwerte — der Lauf hat den Endpunkt nicht erreicht.');
    } else {
        lines.push('p95:            ' + p95.toFixed(1) + ' ms');
        lines.push('Grenze:         ' + limit + ' ms  ' + (p95 < limit ? '(eingehalten)' : '(UEBERSCHRITTEN)'));
        lines.push('Ziel:           ' + target + ' ms  ' + (p95 < target ? '(erreicht)' : '(nicht erreicht)'));
        if (within !== null) {
            lines.push('unter dem Ziel: ' + within.toFixed(1) + ' % der Abrufe');
        }
        if (p95 >= target && p95 < limit) {
            lines.push('');
            lines.push('Der Lauf ist bestanden. Das Ziel ist eine Beobachtungsgroesse,');
            lines.push('keine Bedingung — siehe docs/dev/load-testing.md.');
        }
    }
    lines.push('');

    return {stdout: lines.join('\n')};
}
