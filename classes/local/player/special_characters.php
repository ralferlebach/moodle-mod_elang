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
 * Provides the special characters offered by the player's insert bar.
 *
 * Learners typing an answer often need accented or non-ASCII letters that are
 * awkward to reach on their keyboard. This provider returns a curated set for
 * the exercise language (the 2.1 "special-character bar" foundation), which an
 * activity can later override with its own list. It is pure data — the player UI
 * renders the bar from it.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class special_characters {
    /**
     * Curated special characters per base language code.
     *
     * @var array<string, string[]>
     */
    private const DEFAULTS = [
        'fr' => ['à', 'â', 'æ', 'ç', 'é', 'è', 'ê', 'ë', 'î', 'ï', 'ô', 'œ', 'ù', 'û', 'ü', 'ÿ'],
        'de' => ['ä', 'ö', 'ü', 'ß'],
        'es' => ['á', 'é', 'í', 'ñ', 'ó', 'ú', 'ü', '¿', '¡'],
        'it' => ['à', 'è', 'é', 'ì', 'í', 'î', 'ò', 'ó', 'ù', 'ú'],
        'pt' => ['á', 'â', 'ã', 'à', 'ç', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú'],
    ];

    /**
     * The curated special characters for a language.
     *
     * @param string $language A language code such as "fr" or "fr-CA".
     * @return string[] The special characters, or an empty list for an unknown language.
     */
    public static function for_language(string $language): array {
        $base = \core_text::strtolower(substr($language, 0, 2));

        return self::DEFAULTS[$base] ?? [];
    }

    /**
     * Resolve the characters to offer: a custom list if given, else the
     * language defaults.
     *
     * @param string $language The exercise language.
     * @param string|null $custom An optional custom set: the characters run together (whitespace ignored).
     * @return string[] The characters to offer, de-duplicated and in order.
     */
    public static function resolve(string $language, ?string $custom = null): array {
        if ($custom !== null && trim($custom) !== '') {
            $characters = preg_split('//u', $custom, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $characters = array_filter($characters, static function (string $character): bool {
                return trim($character) !== '';
            });

            return array_values(array_unique($characters));
        }

        return self::for_language($language);
    }
}
