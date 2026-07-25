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

$string['completiondetail:completionfinishattempt'] = 'Finish an attempt';
$string['completionfinishattempt'] = 'Student must finish an attempt';
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
$string['error:couldnotobtainlock'] = 'Could not obtain a lock for this operation. Please try again.';
$string['error:gapnotinattemptversion'] = 'This gap does not belong to the exercise version of this attempt.';
$string['error:invalidv1cuejson'] = 'This version 1 cue could not be parsed.';
$string['error:noaccesstoattempt'] = 'You do not have access to this attempt.';
$string['error:nomorehints'] = 'No further hints are available for this gap.';
$string['error:nopublishedversion'] = 'This exercise has no published content yet.';
$string['error:responsetoolong'] = 'Your response is too long. The maximum for this gap is {$a} characters.';
$string['error:staleattemptstate'] = 'Your view of this attempt is out of date. Please reload the current state and try again.';
$string['filearea_media'] = 'Media';
$string['filearea_poster'] = 'Poster image';
$string['migratev1:approvalheading'] = 'Migrated, awaiting review';
$string['migratev1:approvebutton'] = 'Approve this migration';
$string['migratev1:approved'] = 'elang {$a} has been marked as approved.';
$string['migratev1:colactivity'] = 'Activity';
$string['migratev1:colalgorithm'] = 'Grading algorithm';
$string['migratev1:colcues'] = 'Cues';
$string['migratev1:colgaps'] = 'Gaps';
$string['migratev1:colissues'] = 'Issues';
$string['migratev1:collearners'] = 'Learners';
$string['migratev1:confirmdecommission'] = 'This IRREVERSIBLY drops the version 1 legacy tables and elang.options. There is no undo. Continue?';
$string['migratev1:confirmmigrate'] = 'This will queue a background task that writes new version 2 data for every activity listed above. The version 1 tables and elang.options are left untouched. Continue?';
$string['migratev1:decommissionblocked'] = 'Decommissioning is still blocked; see the list below.';
$string['migratev1:decommissionblockedintro'] = 'Decommissioning is blocked until:';
$string['migratev1:decommissionbutton'] = 'Drop version 1 legacy data';
$string['migratev1:decommissioned'] = 'Version 1 legacy data has been dropped.';
$string['migratev1:decommissionheading'] = 'Decommission version 1 data';
$string['migratev1:decommissionready'] = 'Every version 1 activity has been migrated and approved. The version 1 legacy tables and elang.options can now be dropped. This is irreversible.';
$string['migratev1:heading'] = 'Migrate version 1 activities';
$string['migratev1:migratebutton'] = 'Migrate these activities';
$string['migratev1:noissues'] = 'None';
$string['migratev1:nonepending'] = 'No version 1 activities are waiting to be migrated.';
$string['migratev1:nonependingapproval'] = 'No migrated activities are waiting for review.';
$string['migratev1:notablespresent'] = 'No version 1 legacy tables were found on this site. There is nothing to migrate.';
$string['migratev1:parseerrorcount'] = '{$a} cue(s) could not be parsed';
$string['migratev1:pendingheading'] = 'Not yet migrated';
$string['migratev1:queued'] = 'The migration task has been queued. It will run on the next cron pass, or immediately via admin/cli/adhoc_task.php --execute.';
$string['migratev1:verifiedclean'] = 'Verified: the migrated data matches the version 1 source with no discrepancies.';
$string['migratev1:verifieddiscrepancies'] = 'Verification found {$a} discrepancy(ies) against the version 1 source:';
$string['migratev1:verifyfailed'] = 'Could not verify this activity: {$a}';
$string['modulename'] = 'Language exercise';
$string['modulename_help'] = 'The language exercise activity lets learners fill in gaps in time-coded subtitles while watching or listening to a video.

Teachers import a WebVTT or SubRip subtitle file, mark words or phrases as gaps, and configure how strictly answers are compared. Learners work through the transcript segment by segment, request graded hints and receive immediate feedback.';
$string['modulenameplural'] = 'Language exercises';
$string['noinstances'] = 'There are no language exercises in this course.';
$string['player:finish'] = 'Finish attempt';
$string['player:finished'] = 'Attempt finished. Score: %score%%';
$string['player:gaplabel'] = 'Gap %gap%';
$string['player:hint'] = 'Show a hint';
$string['player:loaderror'] = 'The exercise could not be loaded. Please reload the page.';
$string['player:loading'] = 'Loading the exercise…';
$string['player:ready'] = 'Exercise ready.';
$string['player:scorelabel'] = 'Score: %score%%';
$string['player:stateaccepted'] = 'Accepted';
$string['player:statecorrect'] = 'Correct';
$string['player:statehinted'] = 'Hint used';
$string['player:stateincorrect'] = 'Incorrect';
$string['player:submitfailed'] = 'Your answer could not be saved. Please try again.';
$string['player:transcriptheading'] = 'Transcript';
$string['pluginadministration'] = 'Language exercise administration';
$string['pluginname'] = 'Language exercise';
$string['privacy:metadata:elang_attempt'] = 'For each attempt at an exercise, the activity stores who made it, when, how far it got, and how it was scored.';
$string['privacy:metadata:elang_attempt:answeredgaps'] = 'How many gaps the learner has answered in this attempt.';
$string['privacy:metadata:elang_attempt:attemptnumber'] = 'The sequential number of this attempt for the user and activity.';
$string['privacy:metadata:elang_attempt:correctgaps'] = 'How many gaps were accepted as correct in this attempt.';
$string['privacy:metadata:elang_attempt:exactgaps'] = 'How many gaps were answered with a character-perfect match in this attempt.';
$string['privacy:metadata:elang_attempt:hintedgaps'] = 'How many gaps the learner requested a hint for in this attempt.';
$string['privacy:metadata:elang_attempt:score'] = 'The score achieved in this attempt.';
$string['privacy:metadata:elang_attempt:state'] = 'Whether the attempt is in progress, finished, or abandoned.';
$string['privacy:metadata:elang_attempt:timefinish'] = 'The time the attempt was finished.';
$string['privacy:metadata:elang_attempt:timemodified'] = 'The time the attempt was last updated.';
$string['privacy:metadata:elang_attempt:timestart'] = 'The time the attempt was started.';
$string['privacy:metadata:elang_attempt:totalgaps'] = 'The total number of gaps in the exercise version this attempt is on.';
$string['privacy:metadata:elang_attempt:userid'] = 'The id of the user who made the attempt.';
$string['privacy:metadata:elang_attempt:versionid'] = 'The exercise version this attempt was made against.';
$string['privacy:metadata:elang_response'] = 'For each gap a learner answers within an attempt, the activity stores the response text and how it was evaluated.';
$string['privacy:metadata:elang_response:accepted'] = 'Whether the response was accepted as correct for this gap.';
$string['privacy:metadata:elang_response:hintlevel'] = 'The highest hint level revealed to the learner for this gap.';
$string['privacy:metadata:elang_response:responsetext'] = 'The text the learner typed for this gap.';
$string['privacy:metadata:elang_response:resultstate'] = 'The classification the evaluator found for this response (exact, word-recognised, incorrect or empty).';
$string['privacy:metadata:elang_response:score'] = 'The points this response contributed, after any hint penalty.';
$string['privacy:metadata:elang_response:timecreated'] = 'The time this response was first submitted.';
$string['privacy:metadata:elang_response:timemodified'] = 'The time this response was last updated.';
$string['privacy:metadata:elang_response:tries'] = 'How many times the learner submitted a response to this gap.';
$string['skeletonnotice'] = 'This activity is an infrastructure skeleton for version 2.0. The player, transcript and answering area are not implemented yet.';
$string['subplugintype_elangscript'] = 'Script handler';
$string['subplugintype_elangscript_plural'] = 'Script handlers';
$string['task:migratev1activities'] = 'Migrate version 1 activities';
