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

$string['allowedlanguages'] = 'Allowed content languages';
$string['allowedlanguages_desc'] = 'The content languages offered when creating or editing an eLang activity. Select none to offer the full language list. An activity keeps its stored language even if you later remove it here.';
$string['allowtranscriptdownload'] = 'Transcript download for learners';
$string['allowtranscriptdownload_help'] = 'When enabled, learners can download the transcript worksheet, with every gap blanked out, as a PDF, Word, OpenDocument or text file.

This is off by default. Teachers can always download the transcript regardless of this setting.';
$string['allowtranscriptdownload_label'] = 'Learners may download the worksheet';
$string['completiondetail:completionfinishattempt'] = 'Finish an attempt';
$string['completionfinishattempt'] = 'Student must finish an attempt';
$string['cuepausemode'] = 'Playback at subtitle boundaries';
$string['cuepausemode:auto'] = 'Automatic';
$string['cuepausemode:nostop'] = 'Never stop';
$string['cuepausemode:stop'] = 'Always stop';
$string['cuepausemode_help'] = 'Whether the medium stops at the end of a subtitle.

* Automatic — playback runs on, and stops at the end of a subtitle only while that subtitle is being worked on, that is after clicking it or one of its gaps, or moving the keyboard focus into one.
* Always stop — playback stops at the end of every subtitle and waits to be resumed.
* Never stop — playback runs through to the end of the medium.';
$string['editcontent'] = 'Edit content';
$string['editor:addcue'] = 'Add cue';
$string['editor:addgap'] = 'Mark gap from selection';
$string['editor:addhint'] = 'Add hint';
$string['editor:addvariant'] = 'Add variant';
$string['editor:algoexact'] = 'Exact match';
$string['editor:algorithm'] = 'Matching';
$string['editor:algowordrecognized'] = 'Recognise close answers';
$string['editor:answers'] = 'Accepted variants';
$string['editor:autosaved'] = 'All changes saved.';
$string['editor:autosaveerror'] = 'Could not save automatically — use Save to retry.';
$string['editor:captureend'] = 'Set end from playback';
$string['editor:capturestart'] = 'Set start from playback';
$string['editor:cueactions'] = 'Cue actions';
$string['editor:cuecount'] = '{$a} cue(s)';
$string['editor:currentmedia'] = 'Current medium:';
$string['editor:deletecue'] = 'Delete cue';
$string['editor:deletegap'] = 'Delete gap';
$string['editor:emptytranscript'] = '(no text yet)';
$string['editor:endtime'] = 'End (ms)';
$string['editor:formatsubrip'] = 'SubRip (.srt)';
$string['editor:formatwebvtt'] = 'WebVTT (.vtt)';
$string['editor:gapcount'] = '{$a} gap(s)';
$string['editor:gaprange'] = 'Gap position (characters)';
$string['editor:gotomedia'] = 'Go to Media';
$string['editor:heading'] = 'Exercise content editor';
$string['editor:hints'] = 'Hints';
$string['editor:hinttext'] = 'Hint text';
$string['editor:hinttype'] = 'Type';
$string['editor:hinttype_firstletter'] = 'First letter';
$string['editor:hinttype_partial'] = 'Partial';
$string['editor:hinttype_solution'] = 'Solution';
$string['editor:hinttype_text'] = 'Free text';
$string['editor:hinttype_translation'] = 'Translation';
$string['editor:hinttype_wordlength'] = 'Word length';
$string['editor:import'] = 'Import subtitles';
$string['editor:importappend'] = 'Append to existing cues';
$string['editor:importapply'] = 'Import';
$string['editor:importcancel'] = 'Cancel';
$string['editor:importcheck'] = 'Check content';
$string['editor:importchecking'] = 'Checking…';
$string['editor:importcuecount'] = 'Cues found';
$string['editor:importduration'] = 'Duration';
$string['editor:importedcues'] = '{$a} cue(s) imported.';
$string['editor:importfilehint'] = 'Choose a WebVTT (.vtt) or SubRip (.srt) file with subtitles.';
$string['editor:importformat'] = 'Format';
$string['editor:importfromfile'] = 'Upload file';
$string['editor:importfromtext'] = 'Paste text';
$string['editor:importgapcount'] = 'Gaps found';
$string['editor:importhint'] = 'Paste WebVTT or SubRip content, then import it as cues.';
$string['editor:importparseerror'] = 'This content could not be read as WebVTT or SubRip.';
$string['editor:importpastedtext'] = 'Pasted text';
$string['editor:importreaderror'] = 'The file could not be read.';
$string['editor:importready'] = 'Ready to import';
$string['editor:importreplace'] = 'Replace all cues';
$string['editor:importreplacedcues'] = 'Cues replaced with {$a} imported cue(s).';
$string['editor:importsource'] = 'Source';
$string['editor:importsummary'] = 'What was found';
$string['editor:insertafter'] = 'Insert cue after';
$string['editor:insertbefore'] = 'Insert cue before';
$string['editor:invalidtime'] = 'Enter a time as mm:ss.SSS, for example 01:05.400.';
$string['editor:loaderror'] = 'The editor could not be loaded. Please reload the page.';
$string['editor:loading'] = 'Loading the editor…';
$string['editor:media'] = 'Media';
$string['editor:mediafile'] = 'Uploaded file';
$string['editor:mediakind'] = 'Medium type';
$string['editor:medianone'] = 'None';
$string['editor:mediaprovider'] = 'Provider';
$string['editor:mediaproviderref'] = 'Provider reference';
$string['editor:mediaproviderrefhint'] = 'Video ID or link in any common form (e.g. youtu.be/…).';
$string['editor:mediasaved'] = 'Medium saved.';
$string['editor:mediaurl'] = 'Direct URL';
$string['editor:nocues'] = 'No cues yet. Add one or import subtitles.';
$string['editor:nocueselected'] = 'Select a cue from the list to edit it.';
$string['editor:nocuesmatch'] = 'No cue matches this search.';
$string['editor:nogaps'] = 'No gaps';
$string['editor:nomedia'] = 'none';
$string['editor:nomedianotice'] = 'Add the video or audio file on the Media tab first. Subtitles are timed against the medium, so the editor needs one before you can work on cues and gaps.';
$string['editor:novideotrack'] = 'This browser cannot decode the video track of this medium (only the audio plays); learners would see a black picture. Please re-encode the file as H.264/MP4 (e.g. with ffmpeg or HandBrake) and upload it again.';
$string['editor:onboardinggaps'] = 'Select a word in a cue and mark it as a gap.';
$string['editor:onboardingimport'] = 'Import WebVTT/SubRip subtitles, or add cues by hand.';
$string['editor:onboardingintro'] = 'Build an exercise in three steps:';
$string['editor:onboardingmedia'] = 'Choose a medium (upload, URL or provider).';
$string['editor:onboardingtitle'] = 'Start your exercise';
$string['editor:onlywarnings'] = 'Only cues with warnings';
$string['editor:parsegaps'] = 'Recognise gap markers: [word] creates a gap with hints allowed, {word} one without.';
$string['editor:penalty'] = 'Penalty';
$string['editor:poster'] = 'Poster image';
$string['editor:preview'] = 'Learner preview';
$string['editor:publish'] = 'Publish';
$string['editor:published'] = 'Version published.';
$string['editor:removehint'] = 'Remove hint';
$string['editor:removevariant'] = 'Remove';
$string['editor:ruleapplied'] = 'Created %count% gaps from the rule.';
$string['editor:ruleapply'] = 'Apply %count% gaps';
$string['editor:ruleerror'] = 'The gaps could not be generated.';
$string['editor:ruleeverynth'] = 'Every nth word';
$string['editor:rulefound'] = 'The rule found %count% gaps.';
$string['editor:rulegenerate'] = 'Generate gaps';
$string['editor:ruleinterval'] = 'Interval (n)';
$string['editor:ruletype'] = 'Gap rule';
$string['editor:rulewordlist'] = 'Words to blank out';
$string['editor:rulewords'] = 'Word list';
$string['editor:save'] = 'Save draft';
$string['editor:saved'] = 'Draft saved.';
$string['editor:saveerror'] = 'The draft could not be saved.';
$string['editor:savemedia'] = 'Save medium';
$string['editor:saving'] = 'Saving…';
$string['editor:searchcues'] = 'Search cues';
$string['editor:selecttext'] = 'Select the word to blank out in the transcript first.';
$string['editor:solution'] = 'Solution';
$string['editor:starttime'] = 'Start (ms)';
$string['editor:transcript'] = 'Transcript';
$string['editor:unsaved'] = 'Unsaved changes';
$string['editor:uploadmedia'] = 'Upload media files';
$string['editor:warnemptysolution'] = 'A gap has no solution';
$string['editor:warnnotranscript'] = 'No text';
$string['editor:warntiming'] = 'End is not after start';
$string['editor:waveform'] = 'Audio waveform';
$string['elang:addinstance'] = 'Add a new language exercise';
$string['elang:attempt'] = 'Attempt a language exercise';
$string['elang:deleteattempts'] = 'Delete learner attempts';
$string['elang:exportreports'] = 'Export reports containing personal data';
$string['elang:exportsolution'] = 'Export the full solution transcript';
$string['elang:exporttranscript'] = 'Export the transcript worksheet as a document';
$string['elang:manage'] = 'Create and edit exercise content';
$string['elang:useregex'] = 'Use regular expressions in accepted answers';
$string['elang:view'] = 'View a language exercise';
$string['elang:viewreports'] = 'View learner reports';
$string['error:attemptnotinprogress'] = 'This attempt is no longer in progress.';
$string['error:couldnotobtainlock'] = 'Could not obtain a lock for this operation. Please try again.';
$string['error:draftrevisionmismatch'] = 'This draft was changed since you loaded it. Please reload and try again.';
$string['error:duplicatecuekey'] = 'Two cues share the key \'{$a}\'; each cue needs a unique key.';
$string['error:duplicategapkey'] = 'Two gaps in one cue share the key \'{$a}\'; each gap needs a unique key.';
$string['error:duplicatehintlevel'] = 'A gap has two hints at level {$a}; each hint level must be unique.';
$string['error:gapnotinattemptversion'] = 'This gap does not belong to the exercise version of this attempt.';
$string['error:invalidcuepausemode'] = 'Choose one of the offered options for playback at subtitle boundaries.';
$string['error:invalidgradingalgorithm'] = 'The grading algorithm \'{$a}\' is not one of exact or wordrecognized.';
$string['error:invalidhinttype'] = 'The hint type \'{$a}\' is not one of the allowed hint types.';
$string['error:invalidisregex'] = 'The regex marker of an answer variant must be 0 or 1.';
$string['error:invalidmediakind'] = 'The chosen media kind is not one of file, url or provider.';
$string['error:invalidpenalty'] = 'A hint penalty must be between 0 and 1.';
$string['error:invalidproviderref'] = '\'{$a}\' is not a recognised video ID or link for this provider.';
$string['error:invalidregexpattern'] = '\'{$a}\' is not a valid regular expression.';
$string['error:invalidsolutionavailability'] = 'Choose one of the offered options for when learners may see the solution transcript.';
$string['error:invalidsourceurl'] = 'Enter a full address starting with http:// or https://, or a YouTube or Vimeo link.';
$string['error:invalidsubtitleposition'] = 'Choose one of the offered options for where the subtitles are shown.';
$string['error:invalidv1cuejson'] = 'This version 1 cue could not be parsed.';
$string['error:negativegapoffset'] = 'A gap offset and length must not be negative.';
$string['error:noaccesstoattempt'] = 'You do not have access to this attempt.';
$string['error:nomorehints'] = 'No further hints are available for this gap.';
$string['error:nopublishedversion'] = 'This exercise has no published content yet.';
$string['error:responsetoolong'] = 'Your response is too long. The maximum for this gap is {$a} characters.';
$string['error:solutionnotavailable'] = 'The solution transcript is not available to you for this activity.';
$string['error:staleattemptstate'] = 'Your view of this attempt is out of date. Please reload the current state and try again.';
$string['error:transcriptnotavailable'] = 'There is no transcript available for you to download in this activity.';
$string['error:unknowngaprule'] = 'Unknown gap rule type \'{$a}\'.';
$string['error:unknownmediaprovider'] = '\'{$a}\' is not one of the supported media providers.';
$string['error:versionnotadraft'] = 'Only a draft version can be edited.';
$string['error:versionnotfound'] = 'That exercise version no longer exists.';
$string['error:versionnotpublishable'] = 'This version cannot be published: {$a}';
$string['export:audienceaftersubmission'] = 'Learners can download this once they have finished an attempt';
$string['export:audiencealways'] = 'Learners can download this at any time';
$string['export:audiencestaff'] = 'Teachers and tutors only — not offered to learners';
$string['export:docx'] = 'Download as Word (DOCX)';
$string['export:downloadpdf'] = 'Download PDF';
$string['export:heading'] = 'Export transcript';
$string['export:intro'] = 'Download the transcript of this exercise in several formats.';
$string['export:moreformats'] = 'More formats';
$string['export:nocontent'] = 'There is no published transcript to export yet.';
$string['export:odt'] = 'Download as OpenDocument (ODT)';
$string['export:pdf'] = 'Download as PDF';
$string['export:solution'] = 'Solution transcript';
$string['export:solutionhint'] = 'The full text with every gap solution shown.';
$string['export:text'] = 'Download as text';
$string['export:versionnote'] = 'Exports are based on the currently published version of this exercise.';
$string['export:worksheet'] = 'Worksheet (gaps blanked out)';
$string['export:worksheethint'] = 'The text with every gap blanked out. Ready to hand out as learner material.';
$string['exporttranscript'] = 'Export transcript';
$string['filearea_media'] = 'Media';
$string['filearea_poster'] = 'Poster image';
$string['gradingheading'] = 'Answer grading';
$string['import:badtiming'] = 'Could not read the timing line: {$a}';
$string['import:emptytranscript'] = 'Skipped a cue with no transcript text.';
$string['jarothreshold'] = 'Fuzzy-match threshold';
$string['jarothreshold_help'] = 'For gaps that recognise close answers (the "word recognised" algorithm), this is the minimum Jaro similarity, from 0 to 1, between the reduced forms for a non-identical answer to still count as correct. 1 requires an identical reduction — no fuzziness — while lower values accept closer near-misses. New versions of this activity start from this value.';
$string['jarothresholdrange'] = 'The threshold must be between 0 and 1.';
$string['language'] = 'Content language';
$string['language_help'] = 'The language or script code of the exercise content, for example de, fr, zh-Hans or ja. It controls how answers are compared, including case folding and transliteration. Leave it empty for generic handling. New versions of this activity start from this value.';
$string['language_none'] = 'Generic (not specified)';
$string['media:cuenote'] = 'Existing subtitles and gaps are kept when you change the medium. Their timings are not adjusted, so check them in the editor afterwards.';
$string['media:current'] = 'Current medium';
$string['media:heading'] = 'Media';
$string['media:intro'] = 'Choose the video or audio this exercise is built on. Subtitles are timed against it, so this comes first.';
$string['media:none'] = 'No medium has been set for this exercise yet.';
$string['media:othersource'] = 'Other source';
$string['media:providerhint'] = 'Recognised providers: {$a}. Any other address is used as a direct media URL.';
$string['media:sourceurl'] = 'Source address';
$string['media:sourceurl_help'] = 'Paste the address of a video instead of uploading a file — a YouTube or Vimeo link, or a direct address of a media file.

An address entered here replaces an uploaded file. Leave it empty to use the upload above.

A provider video is played in the provider\\\'s own frame, which does not report its playback time. Such an exercise always shows the subtitles below the medium and never stops at subtitle boundaries.';
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
$string['nav:exportshort'] = 'Export';
$string['nav:media'] = 'Media';
$string['nav:reports'] = 'Attempts';
$string['nav:subtitles'] = 'Subtitles & gaps';
$string['noinstances'] = 'There are no language exercises in this course.';
$string['overview:attempts'] = 'Attempts';
$string['playbackheading'] = 'Playback and subtitles';
$string['playbackoverlayhint'] = 'A caption over the picture shows only the subtitle currently playing, so playback always pauses at the end of a subtitle that still has gaps to fill. There is nothing to choose here.';
$string['playbackproviderhint'] = 'A YouTube or Vimeo video is played by the provider in its own frame, which does not report its playback time. Such an exercise always shows the subtitles below the medium and never stops at subtitle boundaries, whatever is chosen above. Uploaded files and direct media URLs honour both settings.';
$string['player:check'] = 'Check answer';
$string['player:finish'] = 'Finish attempt';
$string['player:finished'] = 'Attempt finished. Score: %score%%';
$string['player:finishincomplete'] = '{$a} gap(s) are still empty. Finish the attempt anyway?';
$string['player:gaplabel'] = 'Gap %gap%';
$string['player:gaplink'] = 'Open link';
$string['player:hint'] = 'Show a hint';
$string['player:loaderror'] = 'The exercise could not be loaded. Please reload the page.';
$string['player:loading'] = 'Loading the exercise…';
$string['player:nocontent'] = 'No exercise content has been published yet. Please check back later.';
$string['player:novideotrack'] = 'Your browser cannot display the video track of this medium; the audio will still play. Please inform your teacher.';
$string['player:outdatedattempt'] = 'This exercise has been updated since you started this attempt. You are continuing on the earlier content; finish this attempt to work with the updated exercise next time.';
$string['player:progress'] = '{$a->done} of {$a->total} gaps answered';
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
$string['privacy:metadata:elang'] = 'For each activity, the record of who signed off the one-way migration of its 1.x content.';
$string['privacy:metadata:elang:migrationapproveduserid'] = 'The user who approved the migration of this activity from mod_elang 1.x. Stored so the sign-off remains auditable.';
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
$string['privacy:metadata:elang_version'] = 'For each content version, the activity stores which user last modified it.';
$string['privacy:metadata:elang_version:usermodified'] = 'The user who last modified this content version. Stored for auditing who edited the exercise content.';
$string['provider:vimeo'] = 'Vimeo';
$string['provider:youtube'] = 'YouTube';
$string['report:actions'] = 'Actions';
$string['report:answered'] = 'Answered';
$string['report:attemptnumber'] = 'Attempt';
$string['report:back'] = 'Back to all attempts';
$string['report:correct'] = 'Correct';
$string['report:delete'] = 'Delete';
$string['report:deleteconfirm'] = 'Permanently delete this attempt and all of its responses? This cannot be undone.';
$string['report:deleted'] = 'The attempt was deleted.';
$string['report:exact'] = 'Exact';
$string['report:export'] = 'Export';
$string['report:filterany'] = 'Any';
$string['report:filterapply'] = 'Apply filters';
$string['report:filterattempt'] = 'Attempt number';
$string['report:filterfrom'] = 'Started from';
$string['report:filterrangeerror'] = 'The end of the range is before its start.';
$string['report:filterreset'] = 'Clear filters';
$string['report:filterstate'] = 'State';
$string['report:filterto'] = 'Started until';
$string['report:filteruser'] = 'Person';
$string['report:finished'] = 'Finished';
$string['report:heading'] = 'Attempts';
$string['report:hinted'] = 'With hint';
$string['report:hints'] = 'Hint level';
$string['report:kpianswered'] = 'Answered';
$string['report:kpiattempts'] = 'Attempts shown';
$string['report:kpiaverage'] = 'Average score (finished)';
$string['report:kpicorrect'] = 'Accepted';
$string['report:kpiexact'] = 'Exactly right';
$string['report:kpifinished'] = 'Finished';
$string['report:kpihinted'] = 'Used a hint';
$string['report:kpihintedgaps'] = 'Needed a hint';
$string['report:noattempts'] = 'No attempts yet.';
$string['report:nogaps'] = 'The version this attempt was taken on has no gaps.';
$string['report:nomatchingattempts'] = 'No attempt matches these filters.';
$string['report:noresponse'] = 'Not answered';
$string['report:response'] = 'Response';
$string['report:result'] = 'Result';
$string['report:result_empty'] = 'Empty';
$string['report:result_exact'] = 'Exact';
$string['report:result_incorrect'] = 'Incorrect';
$string['report:result_none'] = '—';
$string['report:result_wordrecognized'] = 'Recognised';
$string['report:score'] = 'Score';
$string['report:solution'] = 'Solution';
$string['report:started'] = 'Started';
$string['report:state'] = 'State';
$string['report:state_abandoned'] = 'Abandoned';
$string['report:state_finished'] = 'Finished';
$string['report:state_inprogress'] = 'In progress';
$string['report:transcript'] = 'Transcript';
$string['report:tries'] = 'Tries';
$string['report:user'] = 'Person';
$string['report:view'] = 'View';
$string['reports'] = 'Reports';
$string['solutionavailability'] = 'Solution transcript for learners';
$string['solutionavailability:aftersubmission'] = 'After the attempt is finished';
$string['solutionavailability:always'] = 'Any time';
$string['solutionavailability:never'] = 'Never';
$string['solutionavailability_help'] = 'When learners may download the full transcript with every gap solution shown.

* Never — only teachers can download it.
* After the attempt is finished — a learner may download it once they have finished an attempt at this activity.
* Any time — a learner may download it before answering as well.

Teachers can always download it regardless of this setting.';
$string['subplugintype_elangscript'] = 'Script handler';
$string['subplugintype_elangscript_plural'] = 'Script handlers';
$string['subtitleposition'] = 'Subtitle display';
$string['subtitleposition:below'] = 'Below the medium';
$string['subtitleposition:overlaybottom'] = 'On the medium — bottom';
$string['subtitleposition:overlaytop'] = 'On the medium — top';
$string['subtitleposition_help'] = 'Where the interactive subtitles are shown.

* Below the medium — the whole transcript sits under the medium in its own scrolling area, following playback.
* On the medium, bottom / top — only the subtitle currently playing is drawn over the medium.

An audio-only medium has no picture to draw on, so it always uses the display below the medium. The setting itself is kept, and applies again as soon as the activity uses a video.';
$string['task:migratev1activities'] = 'Migrate version 1 activities';
$string['transcriptheading'] = 'Transcript for learners';
$string['validate:emptysolution'] = 'The solution for {$a} is empty.';
$string['validate:hintlevels'] = 'The hint levels for {$a} are not a contiguous sequence starting at 1.';
$string['validate:nocues'] = 'The version has no cues.';
$string['validate:nogaps'] = 'The version has no gaps to answer.';
$string['validate:nonpositivelength'] = 'The character length of {$a} must be positive.';
$string['validate:rangeoutside'] = 'The character range of {$a} lies outside its transcript.';
$string['validate:rangeoverlap'] = 'The character range of {$a} overlaps another gap.';
$string['validate:unknownalgorithm'] = 'The grading algorithm "{$a->algorithm}" for {$a->where} is not recognised.';
$string['validate:where'] = 'gap {$a->gapkey} in cue {$a->cuekey}';
$string['verify:algorithmmismatch'] = 'Gap {$a->gapkey}: grading algorithm is "{$a->actual}", expected "{$a->expected}".';
$string['verify:attemptcount'] = 'The number of migrated attempts is {$a->actual}, expected {$a->expected} distinct 1.x learners.';
$string['verify:jarothreshold'] = 'The answer-comparison threshold is {$a->actual}, expected {$a->expected}.';
$string['verify:missingattempt'] = 'User {$a}: expected a migrated attempt, found none.';
$string['verify:missingcue'] = 'Cue {$a}: the migrated cue is missing.';
$string['verify:missinggap'] = 'Gap {$a}: the migrated gap is missing.';
$string['verify:missinghint'] = 'Gap {$a}: version 1 allowed help here, but no hint was migrated.';
$string['verify:orphancue'] = 'Cue {$a}: no corresponding version 1 cue was found.';
$string['verify:orphangap'] = 'Gap {$a}: no corresponding version 1 gap was found.';
$string['verify:rangemismatch'] = 'Gap {$a}: the character range does not match the version 1 source.';
$string['verify:responsecount'] = 'User {$a->userid}: the number of migrated responses is {$a->actual}, expected {$a->expected}.';
$string['verify:solutionmismatch'] = 'Gap {$a->gapkey}: the solution is "{$a->actual}", expected "{$a->expected}".';
$string['verify:transcriptmismatch'] = 'Cue {$a}: the transcript does not match the version 1 source.';
$string['verify:unexpectedhint'] = 'Gap {$a}: version 1 disallowed help here, but a hint was migrated.';
