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
 * Registers two things under Site administration > Plugins > Activity
 * modules > elang:
 *
 * - **admin_migrate_v1.php** as an admin_externalpage rather than an
 *   admin_settingpage, because it is an action page and not a settings form.
 *   It requires moodle/site:config, the capability admin_externalpage_setup()
 *   enforces for every page nested under $ADMIN; no plugin-specific capability
 *   was created for it.
 * - **The settings themselves**: allowedlanguages, which narrows the content
 *   language dropdown to what is actually taught here, and providerconsent,
 *   which decides whether a YouTube or Vimeo frame may be embedded before the
 *   learner has agreed.
 *
 * Both settings are site-wide on purpose. Which languages a site teaches and
 * whether a third party may be contacted without consent are answered once by
 * the institution, not per exercise by whoever creates it.
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
            get_string('migratev1_heading', 'mod_elang'),
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

    // A site-wide decision, not a per-activity one. Whether a provider may be
    // contacted before a learner has agreed is a data-protection question the
    // institution answers once; asking it per exercise would make it a didactic
    // choice, which it is not, and would leave it to whoever happens to create
    // the activity.
    $settings->add(new admin_setting_configcheckbox(
        'mod_elang/providerconsent',
        get_string('providerconsent', 'mod_elang'),
        get_string('providerconsent_desc', 'mod_elang'),
        1
    ));
}
