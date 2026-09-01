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
 * The playback and subtitle settings of an activity, resolved for one medium.
 *
 * The activity stores what the teacher asked for. Not every medium can honour
 * every request: an audio file has no picture to draw captions on, and a
 * provider embed is a cross-origin iframe that exposes neither its playback
 * time nor a way to pause it. This class answers what the player should
 * actually do, without ever changing what is stored — swapping the medium for
 * one that can honour the request brings the original setting back by itself.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class playback_settings {
    /** Subtitles in their own scrolling region under the medium. */
    const POSITION_BELOW = 'below';

    /** Only the active cue, drawn over the lower part of the medium. */
    const POSITION_OVERLAY_BOTTOM = 'overlaybottom';

    /** Only the active cue, drawn over the upper part of the medium. */
    const POSITION_OVERLAY_TOP = 'overlaytop';

    /** Pause at the end of a cue only while that cue is being worked on. */
    const PAUSE_AUTO = 'auto';

    /** Pause at the end of every cue. */
    const PAUSE_STOP = 'stop';

    /** Never pause at a cue boundary. */
    const PAUSE_NOSTOP = 'nostop';

    /**
     * Every subtitle position an activity may store.
     *
     * @return array The valid values, in the order the settings form offers them
     */
    public static function positions(): array {
        return [self::POSITION_BELOW, self::POSITION_OVERLAY_BOTTOM, self::POSITION_OVERLAY_TOP];
    }

    /**
     * Every pause mode an activity may store.
     *
     * @return array The valid values, in the order the settings form offers them
     */
    public static function pausemodes(): array {
        return [self::PAUSE_AUTO, self::PAUSE_STOP, self::PAUSE_NOSTOP];
    }

    /**
     * Normalise a stored subtitle position, falling back to the default.
     *
     * @param string $position The stored value, possibly empty or unknown
     * @return string One of the valid positions
     */
    public static function normalise_position(string $position): string {
        return in_array($position, self::positions(), true) ? $position : self::POSITION_BELOW;
    }

    /**
     * Normalise a stored pause mode, falling back to the default.
     *
     * @param string $pausemode The stored value, possibly empty or unknown
     * @return string One of the valid pause modes
     */
    public static function normalise_pausemode(string $pausemode): string {
        return in_array($pausemode, self::pausemodes(), true) ? $pausemode : self::PAUSE_AUTO;
    }

    /**
     * Whether a medium gives the player a usable playback clock.
     *
     * A file or direct URL becomes a real audio/video element, whose time the
     * player reads and sets. A provider embed does not, and neither does an
     * activity with no medium at all.
     *
     * @param string $mediakind The version's media kind: file, url, provider, or empty
     * @return bool True when playback time is readable
     */
    public static function has_playback_clock(string $mediakind): bool {
        return $mediakind === 'file' || $mediakind === 'url';
    }

    /**
     * What the player should actually do for one medium.
     *
     * @param string $position The stored subtitle position
     * @param string $pausemode The stored pause mode
     * @param string $mediakind The version's media kind: file, url, provider, or empty
     * @param bool $audioonly Whether the medium carries no picture
     * @return array The resolved position and pause mode, keyed 'subtitleposition' and 'cuepausemode'
     */
    public static function resolve(
        string $position,
        string $pausemode,
        string $mediakind,
        bool $audioonly
    ): array {
        $position = self::normalise_position($position);
        $pausemode = self::normalise_pausemode($pausemode);

        // Nothing to draw an overlay on: an audio track has no picture, and a
        // provider embed is a foreign document this page cannot draw inside.
        if ($audioonly || !self::has_playback_clock($mediakind)) {
            $position = self::POSITION_BELOW;
        }

        // Stopping at a cue boundary needs both a clock to watch and a pause
        // command to send; a provider embed offers neither.
        if (!self::has_playback_clock($mediakind)) {
            $pausemode = self::PAUSE_NOSTOP;
        }

        return [
            'subtitleposition' => $position,
            'cuepausemode' => $pausemode,
        ];
    }
}
