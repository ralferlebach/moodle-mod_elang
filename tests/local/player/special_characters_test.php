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

namespace mod_elang\local\player;

/**
 * Tests for the special-character provider.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\player\special_characters
 */
final class special_characters_test extends \advanced_testcase {
    /**
     * A known language returns its curated set; a region subtag is ignored.
     *
     * @return void
     */
    public function test_for_language_returns_curated_set(): void {
        $french = special_characters::for_language('fr');
        $this->assertContains('é', $french);
        $this->assertContains('œ', $french);
        $this->assertContains('ç', $french);

        // The region subtag does not change the base language.
        $this->assertSame($french, special_characters::for_language('fr-CA'));

        $german = special_characters::for_language('de');
        $this->assertContains('ß', $german);
        $this->assertNotContains('ç', $german);
    }

    /**
     * An unknown language returns an empty set.
     *
     * @return void
     */
    public function test_unknown_language_is_empty(): void {
        $this->assertSame([], special_characters::for_language('xx'));
    }

    /**
     * A custom set overrides the language default, split into de-duplicated
     * characters.
     *
     * @return void
     */
    public function test_custom_set_overrides_and_deduplicates(): void {
        $characters = special_characters::resolve('de', 'áé é ñ');

        $this->assertSame(['á', 'é', 'ñ'], $characters);
    }

    /**
     * An empty or whitespace-only custom set falls back to the language default.
     *
     * @return void
     */
    public function test_blank_custom_set_falls_back_to_language(): void {
        $this->assertSame(special_characters::for_language('fr'), special_characters::resolve('fr', '   '));
        $this->assertSame(special_characters::for_language('fr'), special_characters::resolve('fr', null));
    }
}
