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

namespace mod_elang\plugininfo;

/**
 * Plugininfo class for the elangscript subplugin type.
 *
 * elangscript subplugins provide script-specific answer normalisation and
 * transliteration for languages whose writing system does not reduce to Latin
 * base letters through diacritic stripping (e.g. Korean, Chinese, Japanese,
 * Sanskrit, Cyrillic). See classes/local/grading/script_handler.php for the
 * interface subplugins implement and classes/local/grading/script_handler_manager.php
 * for how they are discovered and selected.
 *
 * The default, core-provided handling for Latin-script languages does not use
 * this mechanism; see classes/local/grading/latin_script_handler.php.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class elangscript extends \core\plugininfo\base {
    /**
     * Whether this subplugin type may be uninstalled through the standard UI.
     *
     * @return bool Always true
     */
    public function is_uninstall_allowed() {
        return true;
    }
}
