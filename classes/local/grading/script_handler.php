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
 * Contract for script-specific text handling used by the answer_evaluator.
 *
 * The two grading algorithms (see answer_evaluator) need two different text
 * transformations:
 * - normalise_for_exact() performs technical canonicalisation only (equal-looking
 *   strings must compare equal regardless of their Unicode encoding form), never
 *   leniency. Case, diacritics and punctuation are preserved.
 * - normalise_for_word_recognised() performs the actual leniency: it reduces a
 *   string to the coarser form used to decide whether the same word was
 *   recognised, for example by transliterating to Latin base letters.
 *
 * The default core implementation (latin_script_handler) covers Latin-script
 * languages via Unicode decomposition plus a small fallback table for
 * characters that do not decompose (ß, æ, ø, ð, þ, ł, and similar).
 *
 * Scripts that do not reduce to Latin letters this way — Hangul, Han
 * characters, Kana, Devanagari, Cyrillic and others — need a genuinely
 * different transliteration scheme (Revised Romanization, Pinyin, Romaji,
 * IAST, scientific transliteration, and so on). Rather than bundling all of
 * these into the core plugin, mod_elang leaves them to elangscript subplugins:
 * each subplugin declares the language/script codes it handles and provides
 * its own implementation of this interface.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface script_handler {
    /**
     * Return the language/script codes this handler covers.
     *
     * Codes are matched case-insensitively against elang.language. A handler
     * may cover several related codes, for example ['zh', 'zh-hans', 'zh-hant']
     * for a Chinese handler, or ['ru', 'uk', 'bg', 'sr'] for a Cyrillic handler
     * that treats several Cyrillic-script languages the same way.
     *
     * @return string[] Lower-case language/script codes
     */
    public function get_supported_codes(): array;

    /**
     * Canonicalise text for the exact-match algorithm.
     *
     * Must not remove information a learner could reasonably be expected to
     * get right (case, diacritics, punctuation). Limited to representational
     * canonicalisation such as Unicode normalisation and outer whitespace
     * trimming.
     *
     * @param string $text Raw response or solution text
     * @return string Canonicalised text
     */
    public function normalise_for_exact(string $text): string;

    /**
     * Reduce text for the word-recognised algorithm.
     *
     * This is where leniency happens: diacritic stripping, transliteration to
     * a comparable base form, case folding, and equivalent-punctuation
     * unification (for example treating straight and curly apostrophes as the
     * same character).
     *
     * @param string $text Raw response or solution text
     * @return string Reduced text used for lenient comparison
     */
    public function normalise_for_word_recognised(string $text): string;
}
