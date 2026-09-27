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

namespace mod_elang\external;

use core_external\external_api;

/**
 * What every external function of this plugin must do, checked for all of them
 * at once.
 *
 * The individual function tests each cover one endpoint well. What they cannot
 * cover is the endpoint nobody wrote a test for: a new function added later
 * with a missing capability declaration, or one that authorises the activity
 * but not the object it was handed. This walks db/services.php instead of a
 * hand-written list, so a function that is added without its guards fails here
 * rather than in a review.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\attempt_helper
 * @covers     \mod_elang\external\authoring_helper
 */
final class security_contract_test extends \advanced_testcase {
    /**
     * The capabilities this plugin defines.
     *
     * Read from db/access.php rather than listed here: a list would be one more
     * thing to keep in step, and keeping things in step by hand is what this
     * class exists to stop relying on.
     *
     * @return array The capability names
     */
    private static function declared_capabilities(): array {
        global $CFG;

        $capabilities = [];
        require($CFG->dirroot . '/mod/elang/db/access.php');

        return array_keys($capabilities);
    }

    /**
     * The declared services of this plugin.
     *
     * @return array Service name => definition
     */
    private function services(): array {
        global $CFG;

        $functions = [];
        require($CFG->dirroot . '/mod/elang/db/services.php');

        return $functions;
    }

    /**
     * Every declared function names a class that really implements the
     * external API contract, and declares the capability it needs.
     *
     * A missing 'capabilities' entry does not block the call — Moodle uses it
     * to tell a mobile client what it may attempt — but its absence is a
     * reliable sign that nobody thought about who is allowed in.
     *
     * @return void
     */
    public function test_every_declared_function_is_complete(): void {
        $services = $this->services();
        $this->assertNotEmpty($services);

        foreach ($services as $name => $definition) {
            $this->assertStringStartsWith('mod_elang_', $name);

            $class = $definition['classname'];
            $this->assertTrue(class_exists($class), "$name names a class that does not exist: $class");
            $this->assertTrue(is_subclass_of($class, external_api::class), "$class is not an external_api");

            foreach (['execute', 'execute_parameters', 'execute_returns'] as $method) {
                $this->assertTrue(method_exists($class, $method), "$class is missing $method()");
            }

            $this->assertArrayHasKey('capabilities', $definition, "$name declares no capability");
            $this->assertNotEmpty($definition['capabilities'], "$name declares an empty capability");

            // The capability has to be one this plugin actually defines. A
            // renamed or mistyped one here is invisible: Moodle uses the field
            // to tell a mobile client what it may attempt, so a wrong value
            // produces no error anywhere — just a client that gives up on a
            // function it was allowed to call, or offers one it was not.
            foreach (explode(',', (string) $definition['capabilities']) as $capability) {
                $capability = trim($capability);
                if ($capability === '') {
                    continue;
                }
                $this->assertContains(
                    $capability,
                    self::declared_capabilities(),
                    "$name declares '$capability', which db/access.php does not define."
                );
            }
            $this->assertArrayHasKey('type', $definition, "$name declares no type");
            $this->assertContains($definition['type'], ['read', 'write'], "$name has an odd type");
        }
    }

    /**
     * Build two learners with an attempt each, in one activity.
     *
     * @return array The two attempts and the two users
     */
    private function two_learners(): array {
        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $elang = $generator->create_instance(['course' => $course->id]);
        $version = $generator->create_version(['elangid' => $elang->id, 'status' => 'published']);
        $cue = $generator->create_cue(['versionid' => $version->id, 'transcript' => 'Le chat dort']);
        $generator->create_gap(['cueid' => $cue->id, 'solution' => 'chat']);

        $manager = new \mod_elang\local\domain\attempt_manager(
            new \mod_elang\local\grading\answer_evaluator(
                new \mod_elang\local\grading\script_handler_manager()
            )
        );

        $mine = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $theirs = $this->getDataGenerator()->create_and_enrol($course, 'student');

        $myattempt = $manager->start_attempt((int) $elang->id, (int) $mine->id, (int) $version->id);
        $theirattempt = $manager->start_attempt((int) $elang->id, (int) $theirs->id, (int) $version->id);

        return [$mine, $myattempt, $theirs, $theirattempt, $elang, $version];
    }

    /**
     * No attempt-scoped function accepts somebody else's attempt.
     *
     * Holding mod/elang:attempt in the activity is not enough: the capability
     * is held by every learner in the course, so authorising on it alone would
     * let any of them read or write any other's work by guessing an id.
     *
     * @return void
     */
    public function test_no_attempt_function_accepts_a_foreign_attempt(): void {
        $this->resetAfterTest();

        [$mine, , , $theirattempt] = $this->two_learners();
        $this->setUser($mine);

        $calls = [
            'get_attempt_exercise' => fn(int $id) => get_attempt_exercise::execute($id),
            'get_attempt_cues' => fn(int $id) => get_attempt_cues::execute($id, 0, 10),
            'get_attempt_state' => fn(int $id) => get_attempt_state::execute($id),
            'finish_attempt' => fn(int $id) => finish_attempt::execute($id),
        ];

        foreach ($calls as $name => $call) {
            try {
                $call((int) $theirattempt->id);
                $this->fail("$name accepted another learner's attempt.");
            } catch (\moodle_exception $e) {
                $this->assertNotEmpty($e->getMessage(), "$name failed without saying why.");
            }
        }
    }

    /**
     * No authoring function accepts a learner.
     *
     * @return void
     */
    public function test_no_authoring_function_accepts_a_learner(): void {
        $this->resetAfterTest();

        [$mine, , , , , $version] = $this->two_learners();
        $this->setUser($mine);

        $versionid = (int) $version->id;
        $calls = [
            'get_version_content' => fn() => get_version_content::execute($versionid),
            'publish_version' => fn() => publish_version::execute($versionid),
            'preview_import' => fn() => preview_import::execute($versionid, 'WEBVTT', false),
            'save_draft_version' => fn() => save_draft_version::execute($versionid, -1, []),
        ];

        foreach ($calls as $name => $call) {
            try {
                $call();
                $this->fail("$name accepted a learner.");
            } catch (\moodle_exception $e) {
                $this->assertNotEmpty($e->getMessage(), "$name failed without saying why.");
            }
        }
    }

    /**
     * Answering and hinting reject a gap from outside the attempted version.
     *
     * The attempt is the caller's own, so ownership alone lets the call
     * through; what must not be assumed is that the gap it names belongs to
     * the exercise being attempted.
     *
     * @return void
     */
    public function test_answering_rejects_a_gap_from_another_exercise(): void {
        $this->resetAfterTest();

        [$mine, $myattempt] = $this->two_learners();

        // A second activity, with a gap of its own.
        $course = $this->getDataGenerator()->create_course();
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $other = $generator->create_instance(['course' => $course->id]);
        $otherversion = $generator->create_version(['elangid' => $other->id, 'status' => 'published']);
        $othercue = $generator->create_cue(['versionid' => $otherversion->id, 'transcript' => 'Le chien court']);
        $othergap = $generator->create_gap(['cueid' => $othercue->id, 'solution' => 'chien']);

        $this->setUser($mine);

        try {
            submit_response::execute((int) $myattempt->id, (int) $othergap->id, 'chien');
            $this->fail('submit_response accepted a gap from another exercise.');
        } catch (\moodle_exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        try {
            request_hint::execute((int) $myattempt->id, (int) $othergap->id);
            $this->fail('request_hint accepted a gap from another exercise.');
        } catch (\moodle_exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    /**
     * Every declared function is reachable the way Moodle actually calls it.
     *
     * The existing completeness test asks whether the class exists. That is not
     * the same question: Moodle dispatches through the external_functions table,
     * which is filled from db/services.php at install time, and a classname that
     * is merely *written* differently there — an extra escape, a stray
     * namespace prefix, a rename that missed this file — produces a row that
     * resolves to nothing. The plugin still passes every unit test, because
     * tests call the class directly and never go through the table.
     *
     * An audit raised exactly this about generate_rule_gaps. It turned out to be
     * a false alarm: the two escaping styles in that file look different and
     * resolve identically. This test is the answer to the underlying question,
     * which was fair even though the finding was not — nothing checked the
     * registered name against reality.
     *
     * @return void
     */
    public function test_every_declared_function_is_registered_and_resolvable(): void {
        global $DB;

        $services = $this->services();

        foreach ($services as $name => $definition) {
            $registered = $DB->get_record('external_functions', ['name' => $name]);

            $this->assertNotFalse(
                $registered,
                "$name is declared in db/services.php but not registered. "
                    . 'Has the plugin been installed since it was added?'
            );

            $this->assertTrue(
                class_exists($registered->classname),
                "$name is registered as '{$registered->classname}', which does not resolve to a class."
            );

            $this->assertTrue(
                method_exists($registered->classname, $registered->methodname),
                "$name is registered with method '{$registered->methodname}', which that class does not have."
            );

            // The stored name is what the dispatcher uses, so a difference
            // between it and the declaration means one of the two is stale.
            $this->assertSame(
                ltrim($definition['classname'], '\\'),
                $registered->classname,
                "$name is declared and registered under different class names."
            );
        }
    }

    /**
     * Calling a function through Moodle's dispatcher reaches the plugin's code.
     *
     * Not a permission test — the call is expected to be refused, because the
     * caller holds nothing. What matters is *how* it is refused: a missing or
     * unresolvable class fails while Moodle is still looking the function up,
     * with a different error and before any of the plugin's own checks run.
     *
     * @return void
     */
    public function test_the_dispatcher_reaches_the_plugin(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        foreach (array_keys($this->services()) as $name) {
            try {
                external_api::call_external_function($name, [], false);
            } catch (\Throwable $e) {
                // A parameter or permission failure means the function was
                // found and entered. Anything about a missing class or method
                // means it was not.
                $message = $e->getMessage();
                $this->assertStringNotContainsString('does not exist', $message, "$name: $message");
                $this->assertStringNotContainsString('Class', $message, "$name: $message");
            }
        }
    }
}
