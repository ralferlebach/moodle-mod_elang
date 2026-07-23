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

/**
 * Shared script_handler test double for the grading test suite.
 *
 * Not a *_test.php file: Moodle's PHPUnit test discovery only requires files
 * matching that suffix, so a plain fixture class would otherwise never be
 * loaded and any reference to it would fail with "Class ... not found"
 * (confirmed against a real Moodle 4.5 test run). Following the same
 * convention Moodle core itself uses for shared fixtures (see
 * lib/phpunit/tests/fixtures/ and lib/phpunit/tests/advanced_test.php), this
 * file lives under tests/fixtures/ and is loaded explicitly via
 * require_once() from each consuming test's setUpBeforeClass(), rather than
 * relying on autoloading. The namespace matches this file's physical
 * location under tests/, the same rule that applies to classes/.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_elang\fixtures;

use mod_elang\local\grading\script_handler;

/**
 * Minimal script_handler test double used across the grading test suite.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class fake_script_handler implements script_handler {
    /** @var string[] */
    private $codes;

    /** @var bool Whether normalise_for_word_recognised() should use the marker-prefixed reduction. */
    private $usedelegationmarker;

    /**
     * Construct the test double.
     *
     * @param string[] $codes Codes this fake handler claims to support
     * @param bool $usedelegationmarker When true, normalise_for_word_recognised()
     *        lower-cases and prefixes 'fake:', proving delegation actually
     *        happened rather than falling back to Latin rules silently. When
     *        false, it simply trims — enough for pure routing tests that only
     *        check which handler instance was returned.
     */
    public function __construct(array $codes, bool $usedelegationmarker = false) {
        $this->codes = $codes;
        $this->usedelegationmarker = $usedelegationmarker;
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
     * Trivial exact normalisation; unused by these tests but required by the interface.
     *
     * @param string $text Raw response or solution text
     * @return string Trimmed text
     */
    public function normalise_for_exact(string $text): string {
        return trim($text);
    }

    /**
     * Reduction used to detect delegation in tests, or a trivial trim when
     * $usedelegationmarker is false.
     *
     * @param string $text Raw response or solution text
     * @return string Reduced text
     */
    public function normalise_for_word_recognised(string $text): string {
        if (!$this->usedelegationmarker) {
            return trim($text);
        }

        // Deliberately not a Latin-style transliteration: the marker prefix
        // proves that answer_evaluator actually delegates to the handler this
        // manager returns, rather than silently falling back to Latin rules.
        return 'fake:' . mb_strtolower(trim($text), 'UTF-8');
    }
}
