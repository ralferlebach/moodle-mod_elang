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
import {sleep} from 'k6';
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

// The medium is served by mod_elang_pluginfile, which is this plugin's code:
// capability, then whether this person may have this version. Measured
// separately because it answers a different question from the content read and
// would otherwise be hidden inside one average.
const mediaLatency = new Trend('elang_media_latency', true);
const mediaErrors = new Rate('elang_media_errors');

/**
 * How long a cohort takes to arrive, in seconds.
 *
 * Three minutes, from practice: a lesson starts, and the class opens the
 * exercise over the next few minutes rather than in the same second. This is
 * the number that turns "200 learners" into a request rate, and getting it
 * wrong by an order of magnitude is what made the previous plan meaningless.
 */
const ARRIVAL_WINDOW = Number(__ENV.ARRIVAL_WINDOW || 180);

/** Seconds between two gaps, from practice. */
const GAP_THINK_TIME = Number(__ENV.GAP_THINK_TIME || 3);

/** How many gaps a learner works through before replaying the medium. */
const GAPS_BEFORE_REPLAY = Number(__ENV.GAPS_BEFORE_REPLAY || 10);

/** Share of learners who open the medium a second time (1:1.5 in practice). */
const MEDIA_REPLAY_PROBABILITY = Number(__ENV.MEDIA_REPLAY || 0.5);

/** How many learners the scenario represents. */
const LEARNERS = Number(__ENV.LEARNERS || __ENV.VUS || 25);

export const options = {
    scenarios: {
        read_content: {
            // Arrival rate, not concurrency. The scenario says how many
            // learners open the exercise and over how long — which is the event
            // a teacher can describe, and the only reading under which
            // "2000 learners" is a sentence about people rather than about
            // sockets.
            //
            // preAllocatedVUs covers the learners who overlap: a session lasts
            // as long as its replay pause, so roughly rate x session length run
            // at once. maxVUs gives headroom rather than letting k6 drop
            // iterations and report a rate it never achieved.
            executor: 'constant-arrival-rate',
            rate: LEARNERS,
            timeUnit: ARRIVAL_WINDOW + 's',
            duration: ARRIVAL_WINDOW + 's',
            preAllocatedVUs: Math.max(10, Math.ceil(LEARNERS / 4)),
            maxVUs: Math.max(50, LEARNERS),
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
    // One iteration is one learner opening the exercise, not one request in an
    // endless loop. That distinction is the whole point of this rewrite: the
    // plan used to run N virtual users with no pause, so "200 learners" meant
    // 200 requests permanently in flight. A class of 200 does not do that —
    // they open the exercise once each, spread over the first minutes of a
    // lesson, and then spend the time answering gaps.
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
    let body = null;
    if (ok) {
        try {
            body = JSON.parse(res.body);
            ok = !body.exception && Array.isArray(body.cues);
        } catch (e) {
            ok = false;
        }
    }
    contentErrors.add(!ok);
    check(res, {'content read ok': () => ok});

    if (!ok || !body) {
        return;
    }

    // The medium, if the exercise has one as a file. Requested with a Range
    // header for the first 64 KB rather than downloaded whole: what belongs to
    // this plugin is the authorisation in mod_elang_pluginfile — capability,
    // then whether this person may have this version — and that runs before a
    // single byte is sent. Transferring a whole video 3000 times would measure
    // the runner's network and nothing else.
    const mediaurl = body.mediafileurl || '';
    if (mediaurl !== '') {
        requestMedium(mediaurl);

        // Half of the learners come back to the medium once during the
        // exercise: they rewind, or replay a sentence they did not catch.
        // A ratio of 1:1.5 was the estimate from practice.
        if (Math.random() < MEDIA_REPLAY_PROBABILITY) {
            // Somewhere in the working time, not immediately. Gap-to-gap is
            // about three seconds, so a replay lands after a handful of gaps.
            sleep(3 + Math.random() * (GAPS_BEFORE_REPLAY * GAP_THINK_TIME));
            requestMedium(mediaurl);
        }
    }
}

/**
 * Ask for the first bytes of the medium, the way a player starts buffering.
 *
 * @param {string} url The media file URL from the content response.
 * @returns {void}
 */
function requestMedium(url) {
    // The content response hands back a pluginfile.php URL, which is the right
    // URL for a browser: it authenticates by session. This plan authenticates
    // by token and has no session, so that URL answers 303 to the login page —
    // which counts as a failed request and makes a healthy run look broken.
    //
    // webservice/pluginfile.php is the same file area reached with a token, and
    // it runs the same mod_elang_pluginfile callback, so the authorisation this
    // measures is unchanged.
    const tokenurl = url.replace('/pluginfile.php/', '/webservice/pluginfile.php/')
        + (url.includes('?') ? '&' : '?') + 'token=' + encodeURIComponent(TOKEN);

    const res = http.get(tokenurl, {
        headers: {Range: 'bytes=0-65535'},
        tags: {name: 'media_file'},
    });

    // 206 is the expected answer to a Range request; 200 means the server
    // ignored the header and sent everything, which is still a success for the
    // authorisation path this measures.
    const ok = res.status === 206 || res.status === 200;
    mediaLatency.add(res.timings.duration);
    mediaErrors.add(!ok);
    check(res, {'medium served': () => ok});
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

        // The media path is reported too. handleSummary replaces k6's own
        // table, so anything not printed here is invisible — which is how the
        // media half of every session managed to fail unnoticed in a run that
        // otherwise looked healthy.
        const media = data.metrics.elang_media_latency;
        const mediaerrors = data.metrics.elang_media_errors;
        lines.push('');
        lines.push('Medium (mod_elang_pluginfile, erste 64 KB):');
        if (media) {
            lines.push('  p95:          ' + media.values['p(95)'].toFixed(1) + ' ms');
            lines.push('  Median:       ' + media.values.med.toFixed(1) + ' ms');
        } else {
            lines.push('  keine Abrufe — hat die Uebung ueberhaupt ein Medium?');
        }
        if (mediaerrors) {
            lines.push('  Fehlerquote:  ' + (mediaerrors.values.rate * 100).toFixed(2) + ' %');
        }

        const sessions = data.metrics.iterations;
        if (sessions) {
            lines.push('');
            lines.push('Sitzungen:      ' + sessions.values.count
                + ' Lernende, angekommen ueber ' + ARRIVAL_WINDOW + ' s');
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
