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

namespace mod_elang\external;

use core_external\external_api;
use mod_elang\fixtures\attempt_test_fixture_builder;

/**
 * Tests for the get_attempt_exercise external function.
 *
 * Extends \advanced_testcase directly — see submit_response_test.php's
 * class docblock for why not \externallib_advanced_testcase. Uses
 * attempt_test_fixture_builder via require_once() in setUpBeforeClass() the
 * same way get_attempt_state_test does — see that fixture's class docblock
 * for why it is a plain class rather than a trait.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\get_attempt_exercise
 */
final class get_attempt_exercise_test extends \advanced_testcase {
    /** @var \stdClass */
    private $cm;

    /** @var \stdClass */
    private $student;

    /** @var \stdClass */
    private $otherstudent;

    /** @var int */
    private $attemptid;

    public static function setUpBeforeClass(): void {
        require_once(__DIR__ . '/../fixtures/attempt_test_fixture.php');
        parent::setUpBeforeClass();
    }

    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        // The shared builder publishes a one-cue, one-gap version (version A)
        // and starts an attempt on it for $student.
        $fixture = attempt_test_fixture_builder::create($this);
        $this->cm = $fixture->cm;
        $this->student = $fixture->student;
        $this->otherstudent = $fixture->otherstudent;
        $this->attemptid = $fixture->attemptid;
    }

    /**
     * The attempt version's identifiers and counts are returned, keyed to
     * the version the attempt is pinned to.
     *
     * @return void
     */
    public function test_returns_attempt_version_counts(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame($this->attemptid, $result['attemptid']);
        $this->assertSame((int) $this->cm->instance, $result['elangid']);
        $this->assertSame((int) $attempt->versionid, $result['versionid']);
        $this->assertSame('fr', $result['language']);
        $this->assertSame(1, $result['totalcues']);
        $this->assertSame(1, $result['totalgaps']);
        $this->assertNotSame('', $result['contenthash']);
        // The French special-character bar is offered.
        $this->assertContains('é', $result['specialcharacters']);
    }

    /**
     * The single most important V2 guarantee: an in-progress attempt keeps
     * reading the version it was started on, even after a newer version has
     * been published. Reading get_published() here instead would show the
     * learner a different exercise than the one their saved responses belong
     * to.
     *
     * @return void
     */
    public function test_returns_the_pinned_version_after_a_newer_one_is_published(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $versiona = (int) $attempt->versionid;

        // Publish a new version B with deliberately different content: two
        // cues and two gaps, versus version A's single cue and gap.
        /** @var \mod_elang_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_elang');
        $versionmanager = new \mod_elang\local\domain\version_manager();
        $draftb = $versionmanager->create_draft((int) $this->cm->instance, $this->student->id);
        $cuebone = $generator->create_cue(['versionid' => $draftb->id]);
        $generator->create_gap(['cueid' => $cuebone->id, 'solution' => 'chien']);
        $cuebtwo = $generator->create_cue(['versionid' => $draftb->id]);
        $generator->create_gap(['cueid' => $cuebtwo->id, 'solution' => 'oiseau']);
        $versionb = $versionmanager->publish($draftb->id, $this->student->id);

        $this->assertNotSame($versiona, (int) $versionb->id, 'sanity: B must be a distinct version');

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        // Still version A's identity and counts, never version B's.
        $this->assertSame($versiona, $result['versionid']);
        $this->assertSame(1, $result['totalcues']);
        $this->assertSame(1, $result['totalgaps']);
    }

    /**
     * A learner cannot read another learner's attempt.
     *
     * @return void
     */
    public function test_rejects_another_users_attempt(): void {
        $this->setUser($this->otherstudent);

        $this->expectException(\moodle_exception::class);
        get_attempt_exercise::execute($this->attemptid);
    }

    /**
     * A user without mod/elang:attempt cannot read the exercise shape.
     *
     * @return void
     */
    public function test_requires_capability(): void {
        global $DB;

        // The mod/elang:attempt capability is not the module's "uservisible"
        // capability (mod/elang:view is), so prohibiting it lets
        // validate_context()'s require_login() pass and the function's own
        // require_capability('mod/elang:attempt', ...) is what denies access.
        $context = \context_module::instance($this->cm->id);
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        assign_capability('mod/elang:attempt', CAP_PROHIBIT, $studentrole->id, $context->id, true);
        $context->mark_dirty();

        $this->expectException(\core\exception\required_capability_exception::class);
        get_attempt_exercise::execute($this->attemptid);
    }

    /**
     * A url-kind medium is returned as a direct URL with its MIME and
     * duration, and no files or poster.
     *
     * @return void
     */
    public function test_returns_url_media(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $DB->set_field('elang_version', 'mediakind', 'url', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaurl', 'https://example.org/clip.mp4', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediamime', 'video/mp4', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaduration', 90, ['id' => $attempt->versionid]);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('url', $result['media']['kind']);
        $this->assertSame('https://example.org/clip.mp4', $result['media']['url']);
        $this->assertSame('video/mp4', $result['media']['mimetype']);
        $this->assertSame(90, $result['media']['duration']);
        $this->assertSame([], $result['media']['files']);
        $this->assertSame('', $result['media']['posterurl']);
    }

    /**
     * A provider-kind medium is returned as provider plus reference, with no
     * file URLs.
     *
     * @return void
     */
    public function test_returns_provider_media(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $DB->set_field('elang_version', 'mediakind', 'provider', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaprovider', 'youtube', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaproviderref', 'dQw4w9WgXcQ', ['id' => $attempt->versionid]);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('provider', $result['media']['kind']);
        $this->assertSame('youtube', $result['media']['provider']);
        $this->assertSame('dQw4w9WgXcQ', $result['media']['providerref']);
        $this->assertSame([], $result['media']['files']);
    }

    /**
     * A file-kind medium returns pluginfile URLs for its media files (keyed
     * by the version id) and for the poster, which can accompany any medium.
     *
     * @return void
     */
    public function test_returns_file_media_and_poster(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $versionid = (int) $attempt->versionid;
        $context = \context_module::instance($this->cm->id);

        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_elang',
            'filearea' => 'media',
            'itemid' => $versionid,
            'filepath' => '/',
            'filename' => 'clip.mp4',
        ], 'fake-video-bytes');
        $fs->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_elang',
            'filearea' => 'poster',
            'itemid' => $versionid,
            'filepath' => '/',
            'filename' => 'poster.jpg',
        ], 'fake-image-bytes');
        $DB->set_field('elang_version', 'mediakind', 'file', ['id' => $versionid]);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('file', $result['media']['kind']);
        $this->assertCount(1, $result['media']['files']);
        $this->assertSame('clip.mp4', $result['media']['files'][0]['filename']);
        $this->assertStringContainsString('pluginfile.php', $result['media']['files'][0]['url']);
        $this->assertStringContainsString("/media/{$versionid}/clip.mp4", $result['media']['files'][0]['url']);
        $this->assertStringContainsString('poster.jpg', $result['media']['posterurl']);
    }

    /**
     * The activity's playback settings reach the player alongside the values
     * actually applied to this medium.
     *
     * @return void
     */
    public function test_returns_the_playback_settings_for_a_video(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $DB->set_field('elang_version', 'mediakind', 'url', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaurl', 'https://example.org/clip.mp4', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediamime', 'video/mp4', ['id' => $attempt->versionid]);
        $DB->set_field('elang', 'subtitleposition', 'overlaytop', ['id' => $attempt->elangid]);
        $DB->set_field('elang', 'cuepausemode', 'stop', ['id' => $attempt->elangid]);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('overlaytop', $result['playback']['subtitleposition']);
        $this->assertSame('stop', $result['playback']['cuepausemode']);
        $this->assertSame('overlaytop', $result['playback']['effectivesubtitleposition']);
        // An overlay always pauses at a cue boundary: the caption shows only
        // the cue that is playing, so running on would take the sentence being
        // answered off the screen. The stored value is reported unchanged.
        $this->assertSame('auto', $result['playback']['effectivecuepausemode']);
    }

    /**
     * A new activity gets the documented defaults without anything being set.
     *
     * @return void
     */
    public function test_playback_settings_default_to_below_and_auto(): void {
        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('below', $result['playback']['subtitleposition']);
        $this->assertSame('auto', $result['playback']['cuepausemode']);
    }

    /**
     * An audio medium keeps the stored overlay setting but is told to render
     * below the medium, because there is no picture to draw on.
     *
     * @return void
     */
    public function test_audio_media_degrade_the_overlay_but_keep_the_setting(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $DB->set_field('elang_version', 'mediakind', 'url', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaurl', 'https://example.org/clip.mp3', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediamime', 'audio/mpeg', ['id' => $attempt->versionid]);
        $DB->set_field('elang', 'subtitleposition', 'overlaybottom', ['id' => $attempt->elangid]);
        $DB->set_field('elang', 'cuepausemode', 'stop', ['id' => $attempt->elangid]);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('overlaybottom', $result['playback']['subtitleposition']);
        $this->assertSame('below', $result['playback']['effectivesubtitleposition']);
        // An audio element reports its time like any other, so it still stops.
        $this->assertSame('stop', $result['playback']['effectivecuepausemode']);
    }

    /**
     * A provider embed reports no playback time, so both settings degrade
     * while the stored values stay untouched.
     *
     * @return void
     */
    public function test_provider_media_degrade_both_playback_settings(): void {
        global $DB;

        $attempt = $DB->get_record('elang_attempt', ['id' => $this->attemptid], '*', MUST_EXIST);
        $DB->set_field('elang_version', 'mediakind', 'provider', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaprovider', 'youtube', ['id' => $attempt->versionid]);
        $DB->set_field('elang_version', 'mediaproviderref', 'dQw4w9WgXcQ', ['id' => $attempt->versionid]);
        $DB->set_field('elang', 'subtitleposition', 'overlaytop', ['id' => $attempt->elangid]);
        $DB->set_field('elang', 'cuepausemode', 'stop', ['id' => $attempt->elangid]);

        $result = get_attempt_exercise::execute($this->attemptid);
        $result = external_api::clean_returnvalue(get_attempt_exercise::execute_returns(), $result);

        $this->assertSame('overlaytop', $result['playback']['subtitleposition']);
        $this->assertSame('stop', $result['playback']['cuepausemode']);
        $this->assertSame('below', $result['playback']['effectivesubtitleposition']);
        $this->assertSame('nostop', $result['playback']['effectivecuepausemode']);
    }
}
