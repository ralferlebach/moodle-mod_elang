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
 * Resolves which content languages the activity settings form offers.
 *
 * The full list comes from Moodle's own language names. The site administrator
 * can narrow it through the mod_elang/allowedlanguages setting; an empty
 * setting means "no restriction". A stored value that is no longer allowed
 * (the admin tightened the list after an activity was created) is always kept
 * in the offered list, so editing that activity never silently drops its
 * language.
 *
 * Keeping this in one place lets mod_form and its tests agree on the exact
 * list without duplicating the restriction logic.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class language_options {
    /**
     * Reduce a full language code to its base code, so lang-pack variants such
     * as de_du or en_us map onto the base language a content language means.
     *
     * @param string $code A language code, possibly a lang-pack variant
     * @return string The base language code
     */
    public static function base_code(string $code): string {
        return explode('_', $code)[0];
    }

    /**
     * Build the language options for the settings form: the empty "generic"
     * option first, then the allowed languages (all of them when unrestricted),
     * plus the current value when it would otherwise be excluded.
     *
     * @param string $current The activity's stored language, '' for a new one
     * @param string|null $allowedcsv The admin setting value (comma-separated
     *        codes), or null to read it from config
     * @return array<string, string> code => display name, '' => generic first
     */
    public static function form_options(string $current = '', ?string $allowedcsv = null): array {
        $all = get_string_manager()->get_list_of_languages();

        if ($allowedcsv === null) {
            $allowedcsv = (string) get_config('mod_elang', 'allowedlanguages');
        }
        $allowed = array_filter(array_map('trim', explode(',', $allowedcsv)), static function (string $code): bool {
            return $code !== '';
        });

        if (empty($allowed)) {
            $options = $all;
        } else {
            $options = [];
            foreach ($allowed as $code) {
                if (isset($all[$code])) {
                    $options[$code] = $all[$code];
                }
            }
            // Never drop the activity's own stored language from the dropdown.
            $currentbase = self::base_code($current);
            if ($current !== '' && !isset($options[$currentbase]) && isset($all[$currentbase])) {
                $options[$currentbase] = $all[$currentbase];
            }
        }

        \core_collator::asort($options);

        return ['' => get_string('language_none', 'mod_elang')] + $options;
    }
}
