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
$plugin->version      = 2026081301;

// Moodle 4.5.0 LTS — hard minimum. The plugin runs on PHP 8.1 (Moodle 4.5)
// through PHP 8.4 (Moodle 5.2+), so no language feature above PHP 8.1 may be used.
$plugin->requires     = 2024100700;

// Supported and validated from Moodle 4.5 LTS through 5.2. Moodle 5.3 is not yet
// stable, so it is not declared here; it is exercised in CI against its
// development branch and the upper bound will be raised once 5.3 is released.
$plugin->supported    = [405, 502];
$plugin->maturity     = MATURITY_ALPHA;
$plugin->release      = '2.0.0-alpha.86';

// No external plugin dependencies. Optional integrations (AI subsystem, OAuth 2
// services, file converters, ffmpeg) are detected at runtime and are never required.
$plugin->dependencies = [];
