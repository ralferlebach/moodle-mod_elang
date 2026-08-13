<?php
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

namespace mod_elang\fixtures;

/**
 * Generates synthetic-but-structurally-realistic V1 (mod_elang 1.x) data at
 * a configurable scale, for exactly the cases the single real sample
 * activity (v1_legacy_schema::insert_sample_activity()) is too small to
 * exercise: many activities, many cues, many learners, and the known V1
 * quirks appearing often enough to matter statistically rather than in one
 * hand-picked instance. See Migration_V1_V2.md chapter 1.1.
 *
 * Requires v1_legacy_schema::create_tables() to have been called first; this
 * class only inserts data, the same separation of concerns
 * v1_legacy_schema itself keeps between schema and the one real sample.
 *
 * The V1 gap-order counter bug (Migration_V1_V2.md chapter 3.1) is
 * reproduced by literally running the same buggy algorithm V1's
 * save_files() (locallib.php:342-396) runs — a single loop variable reused
 * as both the cue array key and, inside the per-cue gap loop, a
 * post-incremented "order" value — rather than by injecting duplicate
 * values after the fact. That is deliberate: it is the actual mechanism
 * that produces the bug in real V1 data (verified against the V1 source,
 * 2026-07-24), not an approximation of its output, and it naturally
 * reproduces the bug exactly when and only when it would really occur
 * (never within a single-gap cue, always when an earlier cue had more gaps
 * than the position gap would otherwise land on).
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_data_simulator {
    /** @var array<string,mixed> Resolved options, see __construct(). */
    private $options;

    /** @var int Next id to hand out across every table this run inserts into. */
    private $nextid = 1000;

    /** @var array{elangids:int[], activities:array} Running summary, returned by generate(). */
    private $summary;

    /**
     * Sentence templates cues are built from, each containing at least one
     * `[bracketed]` (help allowed) or `{braced}` (help disallowed) gap using
     * the same markup convention as V1's own subtitle import. Deliberately
     * more varied than the one real sample activity: multiple gaps in one
     * cue, gaps with a `(link)`, cues with zero gaps, and short cues, so a
     * generated activity does not look like N copies of the same shape.
     *
     * @var string[]
     */
    private const SENTENCE_TEMPLATES = [
        'The [cat] sat on the {mat}.',
        'A [journey] of a thousand {miles} begins with a single [step].',
        'She sells [seashells] by the {seashore}.',
        'Please refer to the [manual (https://example.org/manual)] for details.',
        'This sentence has no gaps at all.',
        '{Practice} makes [perfect].',
        'The [quick] {brown} [fox] jumps over the {lazy} dog.',
        'Water boils at [100] degrees {Celsius} at sea level.',
        'An apple a [day] keeps the {doctor} away.',
        'Better [late] than {never}.',
    ];

    /**
     * Construct a simulator configured for one generation run.
     *
     * @param array $options Any subset of:
     *        - seed (int, default 1): mt_srand() seed, for reproducible runs
     *        - activitycount (int, default 3)
     *        - mincuesperactivity/maxcuesperactivity (int, default 5/12): cues
     *          per generated activity, drawn from this range
     *        - learnersperactivity (int, default 4)
     *        - courseid (int, default 2): id_elang.course value for every
     *          generated activity — the caller is responsible for a matching
     *          course actually existing if that matters for the test
     *        - injectedgecases (bool, default true): see inject_edge_cases()
     */
    public function __construct(array $options = []) {
        $this->options = array_merge([
            'seed' => 1,
            'activitycount' => 3,
            'mincuesperactivity' => 5,
            'maxcuesperactivity' => 12,
            'learnersperactivity' => 4,
            'courseid' => 2,
            'injectedgecases' => true,
        ], $options);
    }

    /**
     * Generate the configured dataset.
     *
     * @return object{elangids: int[], edgecases: string[]} The ids of every
     *         generated activity, and a human-readable list of which edge
     *         cases were actually injected (empty if injectedgecases was
     *         false) — a test can assert against this instead of having to
     *         re-derive which activity/cue/user row is the edge case.
     */
    public function generate(): object {
        global $DB;

        mt_srand((int) $this->options['seed']);

        $this->summary = (object) ['elangids' => [], 'edgecases' => []];

        for ($n = 0; $n < (int) $this->options['activitycount']; $n++) {
            $elangid = $this->generate_activity($n);
            $this->summary->elangids[] = $elangid;
        }

        if ($this->options['injectedgecases']) {
            $this->inject_edge_cases($this->summary->elangids[0]);
        }

        return $this->summary;
    }

    /**
     * Generate one activity: its elang row, a random number of cues within
     * the configured range, and simulated learner activity for each.
     *
     * @param int $index 0-based index of this activity within the run, used
     *        only to vary its name/language so a multi-activity run is
     *        visibly not N identical copies
     * @return int The generated elang.id
     */
    private function generate_activity(int $index): int {
        global $DB;

        $languages = ['en-GB', 'fr-FR', 'de-DE'];
        $language = $languages[$index % count($languages)];

        // Vary tolerance settings across activities so a multi-activity run
        // exercises both branches of the gradingalgorithm mapping decided in
        // Migration_V1_V2.md chapter 1.2 (some activities map to `exact`,
        // some to `wordrecognized`), not just one.
        $lenient = ($index % 2) === 1;

        $elangid = $this->next_id();
        $options = [
            'showlanguage' => true,
            'repeatedunderscore' => 10,
            'titlelength' => 100,
            'limit' => '10',
            'left' => 20,
            'top' => 20,
            'size' => 16,
            'usetransliteration' => $lenient,
            'usecasesensitive' => !$lenient,
            'jaroDistance' => $lenient ? '0.85' : '1',
            'completion_gapfilled' => 50,
            'completion_gapcompleted' => 30,
        ];

        // Writes to the real `elang` table, not a separate legacy one — see
        // v1_legacy_schema's class docblock for why that workaround is gone
        // since 2026072407 added a real, nullable `options` column there
        // (Migration_V1_V2.md chapter 1.2, decision A). grade/
        // completionfinishattempt/jarothreshold/currentversionid are
        // deliberately omitted below for the same reason documented there.
        $DB->insert_record_raw('elang', (object) [
            'id' => $elangid,
            'course' => (int) $this->options['courseid'],
            'name' => 'Simulated activity ' . ($index + 1),
            'intro' => '',
            'introformat' => 1,
            'timecreated' => time(),
            'timemodified' => time(),
            'language' => $language,
            'options' => json_encode($options),
        ], true, false, true);

        $cuecount = mt_rand((int) $this->options['mincuesperactivity'], (int) $this->options['maxcuesperactivity']);
        $cueids = [];
        foreach (range(0, $cuecount - 1) as $cueindex) {
            $cueid = $this->next_id();
            $cueids[] = $cueid;
            $template = self::SENTENCE_TEMPLATES[$cueindex % count(self::SENTENCE_TEMPLATES)];

            // The V1 gap-order bug (see class docblock): real V1's
            // `foreach ($cues as $i => $elt)` resets $i to the cue's own
            // array index at the START of every iteration, regardless of how
            // far the previous cue's inner gap loop advanced it — so each
            // cue's gap-order counter starts fresh at $cueindex here, not
            // carried over from the previous cue. That reset-to-true-index
            // is exactly what produces collisions/gaps in `order` once an
            // earlier cue had more than one gap, and is why it must be
            // reproduced this way rather than as one running counter.
            $ordercounter = $cueindex;
            [$title, $json, $gapcount] = $this->build_cue_from_template($template, $ordercounter);

            // Table elang_cues has real V1 columns begin/end, both SQL
            // reserved words on at least PostgreSQL — see
            // v1_legacy_schema::insert_row()'s docblock for why this must
            // go through it rather than insert_record_raw().
            v1_legacy_schema::insert_row('elang_cues', (object) [
                'id' => $cueid,
                'id_elang' => $elangid,
                'number' => $cueindex + 1,
                'begin' => $cueindex * 5000,
                'end' => $cueindex * 5000 + 4000,
                'title' => $title,
                'json' => $json,
            ]);
        }

        for ($learner = 0; $learner < (int) $this->options['learnersperactivity']; $learner++) {
            $this->simulate_learner($elangid, $cueids, 100 + $learner);
        }

        return $elangid;
    }

    /**
     * Parse one sentence template into a V1-shaped title + json, using the
     * same [bracket]/{brace} markup convention as V1's own subtitle import
     * (locallib.php:357-391) — reimplemented here rather than reused,
     * because the original is a private step inside save_files() tightly
     * coupled to $mform/file handling, not an isolated, callable parser.
     *
     * @param string $template One of self::SENTENCE_TEMPLATES
     * @param int $ordercounter The shared V1 bug counter, passed by
     *        reference and advanced exactly the way locallib.php:381 does
     *        (post-incremented once per gap). Callers must pass in the
     *        current cue's own 0-based index as the STARTING value for each
     *        call — never a value carried over from a previous cue's call —
     *        mirroring how real V1's `foreach ($cues as $i => $elt)` resets
     *        $i to the array key at the start of every iteration regardless
     *        of what the previous iteration's inner loop left it at.
     * @return array{0: string, 1: string, 2: int} [title, json, gap count]
     */
    private function build_cue_from_template(string $template, int &$ordercounter): array {
        $parts = preg_split('/(\[[^\]]*\]|\{[^}]*\})/', $template, -1, PREG_SPLIT_DELIM_CAPTURE);
        $data = [];
        $title = $template;
        $gapcount = 0;

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $first = $part[0];
            $last = $part[strlen($part) - 1];

            if ($first === '[' && $last === ']') {
                $help = true;
            } else if ($first === '{' && $last === '}') {
                $help = false;
            } else {
                $data[] = ['type' => 'text', 'content' => $part];
                continue;
            }

            $inner = substr($part, 1, strlen($part) - 2);
            $link = null;
            if (preg_match('/([^(]*)(\((.*)\))?$/', $inner, $matches) && isset($matches[3]) && $matches[3] !== '') {
                $link = $matches[3];
                $inner = trim($matches[1]);
            }

            $element = ['type' => 'input', 'content' => $inner, 'order' => $ordercounter++, 'help' => $help];
            if ($link !== null) {
                $element['link'] = $link;
            }
            $data[] = $element;
            $gapcount++;
        }

        $title = preg_replace('/(\[[^\]]*\]|\{[^}]*\})/', '...', $template);

        return [$title, json_encode($data), $gapcount];
    }

    /**
     * Simulate one learner's partial progress through an activity: for each
     * cue with at least one gap, randomly leave it untouched, answer it
     * (correctly or incorrectly), or request help — writing elang_users the
     * same way server.php's `check`/`help` endpoints do (overwrite-in-place,
     * elang_check/elang_help append-only log rows without a user reference),
     * not a simplified version of that.
     *
     * @param int $elangid
     * @param int[] $cueids
     * @param int $userid A synthetic user id — the caller is responsible for
     *        a matching user actually existing if that matters for the test
     * @return void
     */
    private function simulate_learner(int $elangid, array $cueids, int $userid): void {
        global $DB;

        foreach ($cueids as $cueid) {
            $cuerecord = $DB->get_record('elang_cues', ['id' => $cueid], '*', MUST_EXIST);
            $elements = json_decode($cuerecord->json, true);

            foreach ($elements as $number => $element) {
                if ($element['type'] !== 'input') {
                    continue;
                }

                $outcome = mt_rand(0, 3); // 0 = untouched, 1 = correct, 2 = wrong, 3 = help.
                if ($outcome === 0) {
                    continue;
                }

                if ($outcome === 3) {
                    $content = '';
                    $help = true;
                    $DB->insert_record('elang_help', [
                        'id_elang' => $elangid,
                        'cue' => $cuerecord->number,
                        'guess' => $element['order'],
                        'info' => $element['content'],
                    ]);
                } else {
                    $help = false;
                    $correct = ($outcome === 1);
                    $submitted = $correct ? $element['content'] : $element['content'] . '_wrong';
                    // On a match V1 overwrites the logged/stored text with
                    // the canonical solution (server.php:335); an incorrect
                    // guess keeps the learner's own text, so info/user only
                    // agree when the guess was right — see
                    // Migration_V1_V2.md chapter 1.2.
                    $content = $correct ? $element['content'] : $submitted;
                    // Table elang_check has a real V1 column named `user` —
                    // a SQL reserved word on at least PostgreSQL, same
                    // reasoning as elang_cues.begin/end, see
                    // v1_legacy_schema::insert_row()'s docblock.
                    v1_legacy_schema::insert_row('elang_check', (object) [
                        'id' => $this->next_id(),
                        'id_elang' => $elangid,
                        'cue' => $cuerecord->number,
                        'guess' => $element['order'],
                        'info' => $element['content'],
                        'user' => $content,
                    ]);
                }

                $existing = $DB->get_record('elang_users', ['id_cue' => $cueid, 'id_user' => $userid]);
                $state = $existing ? json_decode($existing->json, true) : [];
                $state[$number] = ['help' => $help, 'content' => $content];

                if ($existing) {
                    $existing->json = json_encode($state);
                    $DB->update_record('elang_users', $existing);
                } else {
                    $DB->insert_record('elang_users', [
                        'id_elang' => $elangid,
                        'id_cue' => $cueid,
                        'id_user' => $userid,
                        'json' => json_encode($state),
                    ]);
                }
            }
        }
    }

    /**
     * Inject the deliberately-broken edge cases Migration_V1_V2.md chapter
     * 3.1 lists, into the first generated activity, so a migration test can
     * assert it handles each of them without depending on chance to have
     * produced one during ordinary generation.
     *
     * @param int $elangid The activity to inject into (must already have
     *        cues, i.e. have gone through generate_activity())
     * @return void
     */
    private function inject_edge_cases(int $elangid): void {
        global $DB;

        $cuerecord = $DB->get_record('elang_cues', ['id_elang' => $elangid], '*', IGNORE_MULTIPLE);
        if (!$cuerecord) {
            return;
        }

        // An answer text far longer than any reasonable maxlength.
        $longtext = str_repeat('word ', 400);
        $DB->insert_record('elang_users', [
            'id_elang' => $elangid,
            'id_cue' => $cuerecord->id,
            'id_user' => 900001,
            'json' => json_encode(['0' => ['help' => false, 'content' => $longtext]]),
        ]);
        $this->summary->edgecases[] = 'overlong_answer_text (elang_users id_user=900001)';

        // An orphaned response: id_cue pointing at a cue id that does not
        // exist — the real-world cause (Migration_V1_V2.md chapter 3.1) is
        // that V1 deletes and recreates every cue on each save
        // (locallib.php:334-335), so any response tied to a cue id from a
        // previous save becomes unreachable the moment the next save runs.
        $nonexistentcueid = $this->next_id() + 999999;
        $DB->insert_record('elang_users', [
            'id_elang' => $elangid,
            'id_cue' => $nonexistentcueid,
            'id_user' => 900002,
            'json' => json_encode(['0' => ['help' => false, 'content' => 'orphaned']]),
        ]);
        $this->summary->edgecases[] = "orphaned_response (elang_users id_cue={$nonexistentcueid}, no matching elang_cues row)";

        // A gap with an invalid link URL — not http(s), not a Moodle-internal
        // target.
        $linkcue = $this->next_id();
        v1_legacy_schema::insert_row('elang_cues', (object) [
            'id' => $linkcue,
            'id_elang' => $elangid,
            'number' => 9999,
            'begin' => 999000,
            'end' => 999500,
            'title' => 'Follow the ... for more.',
            'json' => json_encode([
                ['type' => 'text', 'content' => 'Follow the '],
                ['type' => 'input', 'content' => 'link', 'order' => 999999, 'help' => true, 'link' => 'javascript:alert(1)'],
                ['type' => 'text', 'content' => ' for more.'],
            ]),
        ]);
        $this->summary->edgecases[] = "invalid_link_url (elang_cues id={$linkcue})";

        // An empty elang_users.json entry (help never requested, nothing
        // ever typed either) — distinct from the "help was used" empty
        // content case, this is simply an untouched-but-recorded gap.
        $DB->insert_record('elang_users', [
            'id_elang' => $elangid,
            'id_cue' => $cuerecord->id,
            'id_user' => 900003,
            'json' => json_encode(['0' => ['help' => false, 'content' => '']]),
        ]);
        $this->summary->edgecases[] = 'empty_untouched_response (elang_users id_user=900003)';
    }

    /**
     * Hand out the next id for this run, across every table — deliberately
     * a single shared counter rather than one per table, so ids stay easy
     * to eyeball as belonging to this simulator run and never collide with
     * v1_legacy_schema::insert_sample_activity()'s fixed ids (1-18) if both
     * are used together in the same test.
     *
     * @return int
     */
    private function next_id(): int {
        return $this->nextid++;
    }
}
