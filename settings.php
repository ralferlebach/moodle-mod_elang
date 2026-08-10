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
 * Site administration settings/pages for mod_elang.
 *
 * No configurable settings exist yet — this file exists solely to register
 * admin_migrate_v1.php (Migration_V1_V2.md chapter 2) as a page under Site
 * administration > Plugins > Activity modules > elang, using
 * admin_externalpage rather than admin_settingpage since it is an action
 * page, not a settings form. Requires moodle/site:config, the same
 * capability admin_externalpage_setup() enforces for every other page
 * nested under $ADMIN — no plugin-specific capability was created for this.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add(
        'modsettings',
        new admin_externalpage(
            'elangmigratev1',
            get_string('migratev1:heading', 'mod_elang'),
            new moodle_url('/mod/elang/admin_migrate_v1.php'),
            'moodle/site:config'
        )
    );
}

if ($ADMIN->fulltree) {
    // Which content languages the activity settings form offers. An empty
    // setting (the default) means "no restriction": the form offers the full
    // Moodle language list. Restricting it here narrows the dropdown to the
    // languages actually taught on this site. The list is built from Moodle's
    // own language names, so it stays in step with installed language packs.
    $languagechoices = get_string_manager()->get_list_of_languages();
    \core_collator::asort($languagechoices);

    $settings->add(new admin_setting_configmultiselect(
        'mod_elang/allowedlanguages',
        get_string('allowedlanguages', 'mod_elang'),
        get_string('allowedlanguages_desc', 'mod_elang'),
        [],
        $languagechoices
    ));
}
