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

namespace mod_elang\local\player;

/**
 * Tests for resolving the stored playback settings against a medium.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\player\playback_settings
 */
final class playback_settings_test extends \basic_testcase {
    /**
     * A video file honours whatever the activity asked for.
     *
     * @return void No return value.
     */
    public function test_a_video_file_honours_every_setting(): void {
        foreach (playback_settings::positions() as $position) {
            foreach (playback_settings::pausemodes() as $pausemode) {
                $resolved = playback_settings::resolve($position, $pausemode, 'file', false);

                $this->assertSame($position, $resolved['subtitleposition']);
                $this->assertSame($pausemode, $resolved['cuepausemode']);
            }
        }
    }

    /**
     * A direct media URL is a real media element too, so it honours everything.
     *
     * @return void No return value.
     */
    public function test_a_direct_url_honours_every_setting(): void {
        $resolved = playback_settings::resolve('overlaytop', 'stop', 'url', false);

        $this->assertSame('overlaytop', $resolved['subtitleposition']);
        $this->assertSame('stop', $resolved['cuepausemode']);
    }

    /**
     * Audio has no picture to draw captions on, so an overlay falls back to the
     * display below the medium — but the pause behaviour is unaffected, because
     * an audio element reports its time like any other.
     *
     * @return void No return value.
     */
    public function test_audio_falls_back_to_the_display_below_the_medium(): void {
        $resolved = playback_settings::resolve('overlaybottom', 'stop', 'file', true);

        $this->assertSame('below', $resolved['subtitleposition']);
        $this->assertSame('stop', $resolved['cuepausemode']);

        $resolved = playback_settings::resolve('overlaytop', 'auto', 'file', true);

        $this->assertSame('below', $resolved['subtitleposition']);
        $this->assertSame('auto', $resolved['cuepausemode']);
    }

    /**
     * A provider embed reports no playback time and takes no pause command, so
     * both settings degrade.
     *
     * @return void No return value.
     */
    public function test_a_provider_embed_degrades_both_settings(): void {
        $resolved = playback_settings::resolve('overlaytop', 'stop', 'provider', false);

        $this->assertSame('below', $resolved['subtitleposition']);
        $this->assertSame('nostop', $resolved['cuepausemode']);
    }

    /**
     * An activity with no medium at all resolves to the safe pair rather than
     * to something the player would try and fail to drive.
     *
     * @return void No return value.
     */
    public function test_no_medium_resolves_to_the_safe_pair(): void {
        $resolved = playback_settings::resolve('overlaybottom', 'auto', '', false);

        $this->assertSame('below', $resolved['subtitleposition']);
        $this->assertSame('nostop', $resolved['cuepausemode']);
    }

    /**
     * Resolving never rewrites what is stored: the same stored pair resolves
     * differently for different media, and comes back in full for a video.
     *
     * @return void No return value.
     */
    public function test_resolution_does_not_consume_the_stored_setting(): void {
        $position = 'overlaytop';
        $pausemode = 'stop';

        $onaudio = playback_settings::resolve($position, $pausemode, 'file', true);
        $onvideo = playback_settings::resolve($position, $pausemode, 'file', false);

        $this->assertSame('below', $onaudio['subtitleposition']);
        $this->assertSame('overlaytop', $onvideo['subtitleposition']);
        $this->assertSame('stop', $onvideo['cuepausemode']);
    }

    /**
     * An unknown or empty stored value normalises to the documented default
     * rather than reaching the player as-is.
     *
     * @return void No return value.
     */
    public function test_unknown_values_normalise_to_the_defaults(): void {
        $this->assertSame('below', playback_settings::normalise_position(''));
        $this->assertSame('below', playback_settings::normalise_position('sideways'));
        $this->assertSame('overlaytop', playback_settings::normalise_position('overlaytop'));

        $this->assertSame('auto', playback_settings::normalise_pausemode(''));
        $this->assertSame('auto', playback_settings::normalise_pausemode('rewind'));
        $this->assertSame('nostop', playback_settings::normalise_pausemode('nostop'));
    }

    /**
     * Only a medium the page owns exposes a playback clock.
     *
     * @return void No return value.
     */
    public function test_only_owned_media_expose_a_playback_clock(): void {
        $this->assertTrue(playback_settings::has_playback_clock('file'));
        $this->assertTrue(playback_settings::has_playback_clock('url'));
        $this->assertFalse(playback_settings::has_playback_clock('provider'));
        $this->assertFalse(playback_settings::has_playback_clock(''));
    }

    /**
     * The offered values are exactly the ones the schema documents, in the
     * order the settings form presents them.
     *
     * @return void No return value.
     */
    public function test_the_offered_values_match_the_schema(): void {
        $this->assertSame(['below', 'overlaybottom', 'overlaytop'], playback_settings::positions());
        $this->assertSame(['auto', 'stop', 'nostop'], playback_settings::pausemodes());
    }
}
