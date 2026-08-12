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
 * Default script_handler for Latin-script languages.
 *
 * This is the fallback handler used whenever no elangscript subplugin claims
 * the activity's language code (see script_handler_manager). It covers German,
 * French, Spanish, Italian, Portuguese, Turkish, the Nordic languages, Polish,
 * Czech, Slovak, Romanian and similarly Latin-alphabet-based languages without
 * requiring a subplugin for each of them.
 *
 * Word-recognised reduction works in two layers:
 * - When the intl extension is available, Unicode NFKD decomposition plus
 *   stripping of combining marks (\p{Mn}) folds the large majority of
 *   precomposed Latin letters (é, ñ, ü, and so on) to their base form
 *   automatically, without a hand-maintained table.
 * - A small manual table then handles the letters that do NOT decompose into
 *   base letter plus combining mark — ß, æ, œ, ø, ð, þ, ł, ħ, ı, ĳ and their
 *   upper-case forms — because Unicode treats these as letters in their own
 *   right, not as accented variants.
 *
 * Without the intl extension only the manual table applies, so diacritics
 * outside that table are not folded. The intl extension is on Moodle's
 * recommended extension list and present on essentially all production
 * installations; this is a documented degrade path, not a silent one — see
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class latin_script_handler implements script_handler {
    /**
     * Letters that Unicode does not decompose into base letter plus combining
     * mark, so NFKD stripping alone cannot fold them. Keys are lower-case;
     * normalise_for_word_recognised() lower-cases its input before applying
     * this table.
     *
     * @var array<string, string>
     */
    private const NONDECOMPOSING_MAP = [
        'ß' => 'ss',
        'æ' => 'ae',
        'œ' => 'oe',
        'ø' => 'o',
        'đ' => 'd',
        'ð' => 'd',
        'þ' => 'th',
        'ł' => 'l',
        'ħ' => 'h',
        'ı' => 'i',
        'ĳ' => 'ij',
    ];

    /**
     * Apostrophe-like characters unified to a single canonical form for the
     * word-recognised algorithm. Keys are the variant, value is the target.
     *
     * @var array<string, string>
     */
    private const APOSTROPHE_MAP = [
        "\u{2019}" => "'",
        "\u{2018}" => "'",
        "\u{02BC}" => "'",
        "\u{0060}" => "'",
        "\u{00B4}" => "'",
    ];

    /**
     * Return the language/script codes this handler covers.
     *
     * Intentionally empty: this handler is the hardcoded default that
     * script_handler_manager falls back to, not looked up by language code
     * like an elangscript subplugin.
     *
     * @return string[] Always an empty array
     */
    public function get_supported_codes(): array {
        return [];
    }

    /**
     * Canonicalise text for the exact-match algorithm.
     *
     * @param string $text Raw response or solution text
     * @return string Canonicalised text
     */
    public function normalise_for_exact(string $text): string {
        $text = $this->to_nfc($text);
        $text = trim($text);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    /**
     * Reduce text for the word-recognised algorithm.
     *
     * @param string $text Raw response or solution text
     * @return string Reduced text used for lenient comparison
     */
    public function normalise_for_word_recognised(string $text): string {
        $text = $this->to_nfc($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, self::APOSTROPHE_MAP);
        $text = $this->strip_diacritics($text);
        $text = strtr($text, self::NONDECOMPOSING_MAP);
        $text = trim($text);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }

    /**
     * Canonically compose text (NFC) so that visually identical strings
     * compare equal regardless of their Unicode encoding form.
     *
     * @param string $text Text to normalise
     * @return string NFC-normalised text, or the original text if the intl
     *                extension is unavailable
     */
    private function to_nfc(string $text): string {
        if (class_exists('Normalizer')) {
            $normalised = \Normalizer::normalize($text, \Normalizer::FORM_C);
            if ($normalised !== false) {
                return $normalised;
            }
        }

        return $text;
    }

    /**
     * Strip combining diacritical marks via Unicode decomposition.
     *
     * @param string $text Text to strip
     * @return string Text with combining marks removed, or the original text
     *                unchanged if the intl extension is unavailable
     */
    private function strip_diacritics(string $text): string {
        if (!class_exists('Normalizer')) {
            return $text;
        }

        $decomposed = \Normalizer::normalize($text, \Normalizer::FORM_KD);
        if ($decomposed === false) {
            return $text;
        }

        return preg_replace('/\p{Mn}/u', '', $decomposed) ?? $decomposed;
    }
}
