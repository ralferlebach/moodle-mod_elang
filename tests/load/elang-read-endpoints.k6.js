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

export const options = {
    scenarios: {
        read_content: {
            executor: 'ramping-vus',
            startVUs: 1,
            stages: [
                {duration: __ENV.RAMPUP || '15s', target: Number(__ENV.VUS || 25)},
                {duration: __ENV.DURATION || '1m', target: Number(__ENV.VUS || 25)},
                {duration: '10s', target: 0},
            ],
            gracefulRampDown: '10s',
        },
    },
    thresholds: {
        // The hard gate is the error rate: fewer than 1% functional errors. The
        // p95 latency is a regression guard, not an absolute SLA — it is
        // dominated by the response payload size, so it scales with the number
        // of cues in the seeded exercise. The default suits the default seed
        // (a few hundred cues); for a stress run of several thousand cues raise
        // it with -e P95=<ms>.
        elang_content_errors: ['rate<0.01'],
        elang_content_latency: ['p(95)<' + Number(__ENV.P95 || 1500)],
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
