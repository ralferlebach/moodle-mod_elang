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
 * Immutable outcome of evaluating one response against one gap.
 *
 * resultstate is the finest classification the evaluator found for the
 * response, independent of the gap's configured acceptance threshold: an
 * exact match is still recorded as RESULTSTATE_EXACT even on a gap configured
 * with the lenient algorithm, and a word-recognised-only match is still
 * recorded as RESULTSTATE_WORDRECOGNIZED even on a gap configured with the
 * strict algorithm. accepted is the separate, policy-level decision of
 * whether that classification is good enough to count as correct for this
 * particular gap. Keeping the two apart lets reports show how precisely an
 * answer was typed regardless of how strict the gap happens to be configured.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grading_result {
    /** @var string The response matched a solution or accepted variant exactly, diacritics and case included. */
    public const RESULTSTATE_EXACT = 'exact';

    /** @var string The response matched only after word-recognised reduction (case folding, transliteration). */
    public const RESULTSTATE_WORDRECOGNIZED = 'wordrecognized';

    /** @var string The response matched neither algorithm against any accepted variant. */
    public const RESULTSTATE_INCORRECT = 'incorrect';

    /** @var string No response was submitted. */
    public const RESULTSTATE_EMPTY = 'empty';

    /** @var string One of the RESULTSTATE_* constants */
    public $resultstate;

    /** @var bool Whether resultstate meets the gap's configured algorithm */
    public $accepted;

    /**
     * @var int|null Id of the elang_gapanswer record that matched, or null when
     * the primary elang_gap.solution matched, or when nothing matched.
     */
    public $matchedgapanswerid;

    /**
     * Construct the result.
     *
     * @param string $resultstate One of the RESULTSTATE_* constants
     * @param bool $accepted Whether resultstate meets the gap's configured algorithm
     * @param int|null $matchedgapanswerid Id of the matched elang_gapanswer record, if any
     */
    public function __construct(string $resultstate, bool $accepted, ?int $matchedgapanswerid = null) {
        $this->resultstate = $resultstate;
        $this->accepted = $accepted;
        $this->matchedgapanswerid = $matchedgapanswerid;
    }
}
