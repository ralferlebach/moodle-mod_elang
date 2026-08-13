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

namespace mod_elang\local\grading;

use mod_elang\fixtures\fake_script_handler;

/**
 * Tests for script_handler_manager routing and fallback.
 *
 * All handlers are injected explicitly, so these tests never depend on
 * whether any elangscript subplugin is actually installed in the test
 * environment.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\grading\script_handler_manager
 */
final class script_handler_manager_test extends \basic_testcase {
    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../../fixtures/fake_script_handler.php');
        parent::setUpBeforeClass();
    }

    /**
     * With no handlers injected, every language falls back to the default
     * Latin-script handler.
     *
     * @return void
     */
    public function test_falls_back_to_latin_handler_when_nothing_claims_the_code(): void {
        $manager = new script_handler_manager([]);

        $this->assertInstanceOf(latin_script_handler::class, $manager->get_handler_for_language('de'));
        $this->assertInstanceOf(latin_script_handler::class, $manager->get_handler_for_language(''));
        $this->assertInstanceOf(latin_script_handler::class, $manager->get_handler_for_language('ko'));
    }

    /**
     * An injected handler is returned for the exact code it declares.
     *
     * @return void
     */
    public function test_exact_code_match_is_routed_to_the_claiming_handler(): void {
        $fake = new fake_script_handler(['ko']);
        $manager = new script_handler_manager([$fake]);

        $this->assertSame($fake, $manager->get_handler_for_language('ko'));
    }

    /**
     * A region- or script-qualified code falls back to the primary subtag.
     *
     * @return void
     */
    public function test_primary_subtag_match_is_routed_to_the_claiming_handler(): void {
        $fake = new fake_script_handler(['zh']);
        $manager = new script_handler_manager([$fake]);

        $this->assertSame($fake, $manager->get_handler_for_language('zh-Hans'));
    }

    /**
     * Matching is case-insensitive on both the declared code and the requested language.
     *
     * @return void
     */
    public function test_matching_is_case_insensitive(): void {
        $fake = new fake_script_handler(['KO']);
        $manager = new script_handler_manager([$fake]);

        $this->assertSame($fake, $manager->get_handler_for_language('Ko'));
    }

    /**
     * A code no installed handler declares falls back to the default handler,
     * never to an unrelated handler.
     *
     * @return void
     */
    public function test_unclaimed_code_falls_back_to_default_not_to_an_unrelated_handler(): void {
        $fake = new fake_script_handler(['ko']);
        $manager = new script_handler_manager([$fake]);

        $this->assertInstanceOf(latin_script_handler::class, $manager->get_handler_for_language('fr'));
    }
}
