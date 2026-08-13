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

/**
 * Minimal script_handler test double shared by script_handler_manager_test
 * and answer_evaluator_test. Not a *_test.php file itself, so PHPUnit never
 * tries to run it as a test case; it is a plain fixture class.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class fake_script_handler implements script_handler {
    /** @var string[] */
    private $codes;

    /**
     * Construct the test double.
     *
     * @param string[] $codes Codes this fake handler claims to support
     */
    public function __construct(array $codes) {
        $this->codes = $codes;
    }

    /**
     * Return the language/script codes this test double claims to support.
     *
     * @return string[] The codes passed to the constructor
     */
    public function get_supported_codes(): array {
        return $this->codes;
    }

    /**
     * Trivial exact normalisation used only to detect delegation in tests.
     *
     * @param string $text Raw response or solution text
     * @return string Trimmed text
     */
    public function normalise_for_exact(string $text): string {
        return trim($text);
    }

    /**
     * Deliberately non-Latin reduction used only to detect delegation in tests.
     *
     * @param string $text Raw response or solution text
     * @return string Marker-prefixed, lower-cased text
     */
    public function normalise_for_word_recognised(string $text): string {
        // Deliberately not a Latin-style transliteration: the marker prefix
        // proves that answer_evaluator actually delegates to the handler this
        // manager returns, rather than silently falling back to Latin rules.
        return 'fake:' . mb_strtolower(trim($text), 'UTF-8');
    }
}
