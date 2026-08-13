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
 * Creates the version 1 (mod_elang 1.x) legacy tables in the test database
 * and populates them, together with the real `elang` table, with the sample
 * dataset supplied by the client on 2026-07-24 (see
 * docs/materials/Migration_V1_V2.md, chapter 1.1) — deliberately not
 * synthetic/randomised data, so migration logic tested against this fixture
 * is tested against the exact bytes a real (if small) V1 activity produced,
 * "[]"/"{}" markup and the known gap-counter bug (duplicate/missing `order`
 * values, see below) included.
 *
 * This is intentionally a plain class with static methods, not a
 * testing_module_generator: the four tables create_tables() creates
 * (elang_cues, elang_users, elang_help, elang_check) are the OLD, pre-2.0
 * schema, not anything mod_elang's own db/install.xml declares — creating
 * and dropping them is entirely the concern of whichever test needs a V1
 * source to migrate from, not something the plugin generator should know
 * about.
 *
 * The activity-level table is `elang` itself, not a separate one: since
 * 2026072407 (Migration_V1_V2.md chapter 1.2, decision A) the real `elang`
 * table carries a nullable `options` column purely so a V1 activity's JSON
 * blob survives from schema upgrade to data migration — the exact situation
 * this fixture models. An earlier version of this class used a separate
 * `elang_v1` table to avoid colliding with the real one before that column
 * existed; with `options` now part of install.xml, that workaround is gone.
 * insert_sample_activity() below relies on grade/completionfinishattempt/
 * jarothreshold all having real column DEFAULTs (100/0/1) so it can omit
 * them entirely and let the database apply the same values a real,
 * not-yet-migrated V1 activity would have after the schema upgrade alone.
 *
 * Field types for the four legacy-only tables match the real V1
 * db/install.xml exactly (mod_elang 1.x, release 2018091012, supplied by
 * the client 2026-07-24 — see Migration_V1_V2.md chapter 1.2), not an
 * approximation.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_legacy_schema {
    /** @var string[] Legacy-only table names created by create_tables(), in creation order. */
    private const TABLES = ['elang_cues', 'elang_users', 'elang_help', 'elang_check'];

    /**
     * Create the four legacy-only V1 tables. Safe to call only once per
     * test; the caller is responsible for dropping them again (see
     * drop_tables()), normally via resetAfterTest(true) rather than an
     * explicit call, since these tables do not exist in install.xml and
     * resetAfterTest() alone will not know to remove them.
     *
     * @return void
     */
    public static function create_tables(): void {
        global $DB;

        $dbman = $DB->get_manager();

        // Field types/nullability below match the real V1 db/install.xml
        // (mod_elang 1.x, release 2018091012, supplied by the client
        // 2026-07-24) exactly, not an approximation — see
        // Migration_V1_V2.md chapter 1.2.
        $cues = new \xmldb_table('elang_cues');
        $cues->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $cues->add_field('id_elang', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $cues->add_field('number', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $cues->add_field('begin', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $cues->add_field('end', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $cues->add_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $cues->add_field('json', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $cues->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $users = new \xmldb_table('elang_users');
        $users->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $users->add_field('id_elang', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $users->add_field('id_cue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $users->add_field('id_user', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $users->add_field('json', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $users->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        // Real V1 schema: UNIQUE(id_cue, id_user) — at most one row per
        // (gap-bearing cue, learner), overwritten on every check/help
        // request rather than accumulating history (Migration_V1_V2.md
        // chapter 1.2). The simulator relies on this being enforced.
        $users->add_index('icueuser', XMLDB_INDEX_UNIQUE, ['id_cue', 'id_user']);

        $help = new \xmldb_table('elang_help');
        $help->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $help->add_field('id_elang', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $help->add_field('cue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $help->add_field('guess', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $help->add_field('info', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $help->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $check = new \xmldb_table('elang_check');
        $check->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $check->add_field('id_elang', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $check->add_field('cue', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $check->add_field('guess', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $check->add_field('info', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $check->add_field('user', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $check->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        foreach ([$cues, $users, $help, $check] as $table) {
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
            $dbman->create_table($table);
        }
    }

    /**
     * Drop the four legacy-only V1 tables again. Normally unnecessary:
     * resetAfterTest(true) drops any table not declared in install.xml at
     * the end of the test. Provided for tests that need a clean slate
     * mid-test without a full reset. Never touches the real `elang` table.
     *
     * @return void
     */
    public static function drop_tables(): void {
        global $DB;

        $dbman = $DB->get_manager();
        foreach (array_reverse(self::TABLES) as $name) {
            $table = new \xmldb_table($name);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }
    }

    /**
     * Insert one row, with every column identifier quoted for the active
     * database driver.
     *
     * Needed because several of the real V1 column names this fixture must
     * reproduce exactly — `begin`/`end` on elang_cues, `user` on
     * elang_check — are SQL reserved words on at least PostgreSQL, and
     * Moodle's own insert_record()/insert_record_raw() never quote column
     * identifiers in the INSERT statements they generate. That is a
     * reasonable assumption for virtually every real Moodle schema (no
     * official plugin uses a reserved word as a column name), just not for
     * this one, faithfully reproducing V1's own 2018 column choices — on
     * MySQL/MariaDB this silently worked anyway (confirmed via CI against
     * both drivers, 2026-07-24); PostgreSQL rejected the resulting
     * `INSERT INTO t (...,begin,end,...)` outright as a syntax error.
     *
     * Always inserts the row's own `id` explicitly — the same thing
     * insert_record_raw()'s $customsequence=true achieves, needed here too
     * since every caller in this fixture depends on stable, predictable ids
     * for cross-references between rows.
     *
     * @param string $table Table name without the site prefix
     * @param \stdClass $record Every property becomes one column; must include `id`
     * @return void
     */
    public static function insert_row(string $table, \stdClass $record): void {
        global $DB;

        $columns = array_keys((array) $record);
        // Chr(96) is a backtick — written this way, not as a literal
        // character in a string, because Moodle's coding standard flags
        // backticks inside string literals (they usually signal accidental
        // shell-exec syntax elsewhere; here it is genuinely just MySQL's
        // identifier-quote character, not an operator).
        $quote = $DB->get_dbfamily() === 'mysql' ? chr(96) : '"';
        $quotedcolumns = array_map(static fn ($name) => $quote . $name . $quote, $columns);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = 'INSERT INTO {' . $table . '} (' . implode(',', $quotedcolumns) . ') VALUES (' . implode(',', $placeholders) . ')';
        $DB->execute($sql, array_values((array) $record));
    }

    /**
     * Populate the real `elang` table and the four legacy-only tables with
     * the sample activity supplied by the client on 2026-07-24: one
     * activity ("Test", id 1), nine cues transcribed from example.srt (see
     * docs/materials/Migration_V1_V2.md), one learner (id_user 2) with
     * partial progress, and the elang_help/elang_check rows that same
     * session produced.
     *
     * Requires create_tables() to have been called first, and — since this
     * writes to the real `elang` table — the site to already be at
     * 2026072407 or later (Migration_V1_V2.md chapter 1.2, decision A),
     * true of any PHPUnit run against this codebase's own install.xml.
     *
     * Every insert goes through insert_record_raw(..., $customsequence = true)
     * rather than the usual insert_record(): the latter always strips any
     * supplied `id` and lets the database assign one, which would break the
     * explicit cross-references between these rows (elang_users.id_cue
     * pointing at a specific elang_cues.id, for example) that this fixture
     * depends on to match the original sample data exactly.
     *
     * @return void
     */
    public static function insert_sample_activity(): void {
        // Columns grade, completionfinishattempt, jarothreshold and
        // currentversionid are deliberately absent below: the first three
        // have real schema DEFAULTs (100/0/1) that the database applies on
        // their own, and currentversionid being unset (NULL) is exactly the
        // "not yet
        // migrated" state this fixture is meant to represent — see the
        // class docblock.
        self::insert_row('elang', (object) [
            'id' => 1,
            'course' => 2,
            'name' => 'Test',
            'intro' => '',
            'introformat' => 1,
            'timecreated' => 1784841437,
            'timemodified' => 1784875496,
            'language' => 'en-GB',
            'options' => '{"showlanguage":true,"repeatedunderscore":10,"titlelength":100,"limit":"10",'
                . '"left":20,"top":20,"size":16,"usetransliteration":false,"usecasesensitive":true,'
                . '"jaroDistance":"1","completion_gapfilled":0,"completion_gapcompleted":0}',
        ]);

        $cues = [
            [10, 1, 1, 0, 2500, 'Welcome to the ... Subtitle File!',
                '[{"type":"text","content":"Welcome to the "},'
                . '{"type":"input","content":"Example","order":0,"help":true},'
                . '{"type":"text","content":" Subtitle File!"}]', ],
            [11, 1, 2, 3000, 6000, 'This is a ... of SRT subtitles.',
                '[{"type":"text","content":"This is a "},'
                . '{"type":"input","content":"demonstration","order":1,"help":false},'
                . '{"type":"text","content":" of SRT subtitles."}]', ],
            [12, 1, 3, 7000, 10500, 'You can use SRT ... to add subtitles to your videos.',
                '[{"type":"text","content":"You can use SRT "},'
                . '{"type":"input","content":"files","order":2,"help":true,'
                . '"link":"https:\/\/de.wikipedia.org\/wiki\/File"},'
                . '{"type":"text","content":" to add subtitles to your videos."}]', ],
            [13, 1, 4, 12000, 15000, "Each subtitle ... consists of a number, a timecode,\nand the subtitle text.",
                '[{"type":"text","content":"Each subtitle "},'
                . '{"type":"input","content":"entry","order":3,"help":false},'
                . '{"type":"text","content":" consists of a number, a timecode,\nand the subtitle text."}]', ],
            [14, 1, 5, 16000, 20000, 'The ... ... is hours:minutes:seconds,milliseconds.',
                '[{"type":"text","content":"The "},'
                . '{"type":"input","content":"timecode","order":4,"help":true},'
                . '{"type":"text","content":" "},'
                . '{"type":"input","content":"format","order":5,"help":false},'
                . '{"type":"text","content":" is hours:minutes:seconds,milliseconds."}]', ],
            // Note the duplicate order:5 (also used by cue 14's second gap,
            // immediately above) and the missing order:7 (skipped entirely,
            // see cue 18 below) — this is the V1 gap-counter bug
            // (locallib.php:342/381) reproducing itself in real output, not
            // a mistake in this fixture. See Migration_V1_V2.md chapter 3.1.
            [15, 1, 6, 21000, 25000, 'You can adjust the ... to match your video.',
                '[{"type":"text","content":"You can adjust the "},'
                . '{"type":"input","content":"timing","order":5,"help":true},'
                . '{"type":"text","content":" to match your video."}]', ],
            [16, 1, 7, 26000, 30000, 'Make sure the ... is clear and readable.',
                '[{"type":"text","content":"Make sure the "},'
                . '{"type":"input","content":"subtitle text","order":6,"help":true},'
                . '{"type":"text","content":" is clear and readable."}]', ],
            [17, 1, 8, 31000, 35000, "And that's how you create an SRT subtitle file!",
                '[{"type":"text","content":"And that\'s how you create an SRT subtitle file!"}]', ],
            [18, 1, 9, 36000, 40000, 'Enjoy adding ... to your videos!',
                '[{"type":"text","content":"Enjoy adding "},'
                . '{"type":"input","content":"subtitles","order":8,"help":false},'
                . '{"type":"text","content":" to your videos!"}]', ],
        ];
        foreach ($cues as [$id, $idelang, $number, $begin, $end, $title, $json]) {
            self::insert_row('elang_cues', (object) [
                'id' => $id,
                'id_elang' => $idelang,
                'number' => $number,
                'begin' => $begin,
                'end' => $end,
                'title' => $title,
                'json' => $json,
            ]);
        }

        $users = [
            [1, 1, 10, 2, '{"1":{"help":true,"content":""}}'],
            [2, 1, 11, 2, '{"1":{"help":false,"content":"demonstration"}}'],
            [3, 1, 12, 2, '{"1":{"help":true,"content":""}}'],
        ];
        foreach ($users as [$id, $idelang, $idcue, $iduser, $json]) {
            self::insert_row('elang_users', (object) [
                'id' => $id,
                'id_elang' => $idelang,
                'id_cue' => $idcue,
                'id_user' => $iduser,
                'json' => $json,
            ]);
        }

        // Neither elang_help nor elang_check carries a user reference — see
        // Migration_V1_V2.md chapter 3, corrected 2026-07-24: these cannot be
        // migrated per-user, only used as an aggregate report note.
        $help = [
            [1, 1, 1, 0, 'Example'],
            [2, 1, 3, 2, 'files'],
        ];
        foreach ($help as [$id, $idelang, $cue, $guess, $info]) {
            self::insert_row('elang_help', (object) [
                'id' => $id,
                'id_elang' => $idelang,
                'cue' => $cue,
                'guess' => $guess,
                'info' => $info,
            ]);
        }

        $check = [
            [1, 1, 2, 1, 'demonstration', 'example'],
            [2, 1, 2, 1, 'demonstration', 'demonstration'],
            [3, 1, 2, 1, 'demonstration', 'demonstration'],
            [4, 1, 3, 2, 'files', 'files'],
        ];
        foreach ($check as [$id, $idelang, $cue, $guess, $info, $user]) {
            self::insert_row('elang_check', (object) [
                'id' => $id,
                'id_elang' => $idelang,
                'cue' => $cue,
                'guess' => $guess,
                'info' => $info,
                'user' => $user,
            ]);
        }
    }
}
