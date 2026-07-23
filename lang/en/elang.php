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
 * English language strings for mod_elang.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['elang:addinstance'] = 'Add a new language exercise';
$string['elang:attempt'] = 'Attempt a language exercise';
$string['elang:deleteattempts'] = 'Delete learner attempts';
$string['elang:exportreports'] = 'Export reports containing personal data';
$string['elang:exporttranscript'] = 'Export the transcript as a document';
$string['elang:manage'] = 'Create and edit exercise content';
$string['elang:useregex'] = 'Use regular expressions in accepted answers';
$string['elang:view'] = 'View a language exercise';
$string['elang:viewreports'] = 'View learner reports';
$string['error:attemptnotinprogress'] = 'This attempt is no longer in progress.';
$string['error:gapnotinattemptversion'] = 'This gap does not belong to the exercise version of this attempt.';
$string['error:noaccesstoattempt'] = 'You do not have access to this attempt.';
$string['error:nomorehints'] = 'No further hints are available for this gap.';
$string['error:nopublishedversion'] = 'This exercise has no published content yet.';
$string['modulename'] = 'Language exercise';
$string['modulename_help'] = 'The language exercise activity lets learners fill in gaps in time-coded subtitles while watching or listening to a video.

Teachers import a WebVTT or SubRip subtitle file, mark words or phrases as gaps, and configure how strictly answers are compared. Learners work through the transcript segment by segment, request graded hints and receive immediate feedback.';
$string['modulenameplural'] = 'Language exercises';
$string['noinstances'] = 'There are no language exercises in this course.';
$string['pluginadministration'] = 'Language exercise administration';
$string['pluginname'] = 'Language exercise';
$string['privacy:metadata:elang_attempt'] = 'For each attempt at an exercise, the activity stores who made it, when, and how it went.';
$string['privacy:metadata:elang_attempt:attemptnumber'] = 'The sequential number of this attempt for the user and activity.';
$string['privacy:metadata:elang_attempt:score'] = 'The score achieved in this attempt.';
$string['privacy:metadata:elang_attempt:state'] = 'Whether the attempt is in progress, finished, or abandoned.';
$string['privacy:metadata:elang_attempt:timefinish'] = 'The time the attempt was finished.';
$string['privacy:metadata:elang_attempt:timestart'] = 'The time the attempt was started.';
$string['privacy:metadata:elang_attempt:userid'] = 'The id of the user who made the attempt.';
$string['privacy:metadata:elang_response'] = 'For each gap a learner answers within an attempt, the activity stores the response text and how it was evaluated.';
$string['privacy:metadata:elang_response:accepted'] = 'Whether the response was accepted as correct for this gap.';
$string['privacy:metadata:elang_response:responsetext'] = 'The text the learner typed for this gap.';
$string['privacy:metadata:elang_response:resultstate'] = 'The classification the evaluator found for this response (exact, word-recognised, incorrect or empty).';
$string['privacy:metadata:elang_response:timecreated'] = 'The time this response was first submitted.';
$string['privacy:metadata:elang_response:tries'] = 'How many times the learner submitted a response to this gap.';
$string['skeletonnotice'] = 'This activity is an infrastructure skeleton for version 2.0. The player, transcript and answering area are not implemented yet.';
$string['subplugintype_elangscript'] = 'Script handler';
$string['subplugintype_elangscript_plural'] = 'Script handlers';
