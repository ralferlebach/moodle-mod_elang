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

namespace mod_elang\local\settings;

/**
 * Tests for the content-language option builder.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\settings\language_options
 */
final class language_options_test extends \advanced_testcase {
    /**
     * A lang-pack variant reduces to its base code.
     *
     * @return void
     */
    public function test_base_code_strips_variant(): void {
        $this->assertSame('de', language_options::base_code('de_du'));
        $this->assertSame('en', language_options::base_code('en'));
        $this->assertSame('', language_options::base_code(''));
    }

    /**
     * With no restriction, the full language list is offered behind the
     * generic option.
     *
     * @return void
     */
    public function test_unrestricted_offers_full_list(): void {
        $this->resetAfterTest();

        $options = language_options::form_options('', '');

        $all = get_string_manager()->get_list_of_languages();
        $this->assertArrayHasKey('', $options);
        $this->assertSame(get_string('language_none', 'mod_elang'), $options['']);
        // Every real language is present (the generic key adds one).
        $this->assertCount(count($all) + 1, $options);
    }

    /**
     * A restriction narrows the offered list to the chosen languages, still
     * behind the generic option.
     *
     * @return void
     */
    public function test_restriction_narrows_list(): void {
        $this->resetAfterTest();

        $options = language_options::form_options('', 'en,fr');

        $this->assertArrayHasKey('', $options);
        $this->assertArrayHasKey('en', $options);
        $this->assertArrayHasKey('fr', $options);
        $this->assertArrayNotHasKey('de', $options);
        $this->assertCount(3, $options);
    }

    /**
     * A stored language that is no longer allowed is kept in the list, so
     * editing that activity never silently drops its language.
     *
     * @return void
     */
    public function test_stored_value_is_never_dropped(): void {
        $this->resetAfterTest();

        $options = language_options::form_options('de', 'en,fr');

        $this->assertArrayHasKey('de', $options);
        $this->assertArrayHasKey('en', $options);
    }

    /**
     * The admin setting is read from config when no explicit value is passed.
     *
     * @return void
     */
    public function test_reads_restriction_from_config(): void {
        $this->resetAfterTest();
        set_config('allowedlanguages', 'en', 'mod_elang');

        $options = language_options::form_options('');

        $this->assertArrayHasKey('en', $options);
        $this->assertArrayNotHasKey('fr', $options);
        $this->assertCount(2, $options);
    }
}
