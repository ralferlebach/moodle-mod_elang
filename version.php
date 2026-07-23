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
 * Plugin version definition for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component    = 'mod_elang';
$plugin->version      = 2026072307;

// Moodle 4.5.0 LTS — hard minimum. The plugin runs on PHP 8.1 (Moodle 4.5)
// through PHP 8.4 (Moodle 5.2+), so no language feature above PHP 8.1 may be used.
$plugin->requires     = 2024100700;

// Tested from Moodle 4.5 LTS up to the 5.3 development branch. Moodle 5.3 is the
// next LTS (code freeze 24 Aug 2026, release 5 Oct 2026); raise the upper bound
// and re-validate against the final stable branch after that date.
$plugin->supported    = [405, 503];
$plugin->maturity     = MATURITY_ALPHA;
$plugin->release      = '2.0.0-alpha.8';

// No external plugin dependencies. Optional integrations (AI subsystem, OAuth 2
// services, file converters, ffmpeg) are detected at runtime and are never required.
$plugin->dependencies = [];
