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

namespace mod_elang\local\migration;

/**
 * Copy a version 1 activity's media files into the version 2 versioned media
 * file areas.
 *
 * Version 1 (mod_elang 1.x, e.g. 2018091012) stored media as Moodle files on
 * the module context, all at itemid 0, in these file managers (confirmed from
 * that release's mod_form.php): 'videos' (one or more of .webm/.ogv/.mp4 — V1
 * allowed several, as browser-fallback encodings of the same clip) and
 * 'poster' (a single image). This copies 'videos' -> the V2 'media' area and
 * 'poster' -> the V2 'poster' area, at itemid = the migrated version's id, so
 * the medium is versioned exactly like the rest of the content.
 *
 * The V1 'subtitle' area is deliberately NOT migrated as media: its VTT/SRT is
 * the source the cue migration already turned into elang_cue/elang_gap rows,
 * so the transcript and its timing live in the versioned content, not in a
 * file the player would re-parse.
 *
 * The copy is non-destructive — the V1 files are left in place — so a
 * migration can be reviewed and, if necessary, re-run against a fresh version
 * before the version 1 legacy data is decommissioned.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class v1_media_migrator {
    /** @var int Version 1 stored every media file at this itemid. */
    private const V1_ITEMID = 0;

    /**
     * Copy an activity's version 1 media and poster into the version 2
     * versioned areas for $versionid, and mark the version as file-kind media
     * when at least one media (video) file was copied.
     *
     * A DB-only activity with no real course module (as simulated in some
     * migration unit tests) has no file areas to copy and is a harmless no-op;
     * every genuine version 1 activity being migrated has a course module.
     *
     * @param int $elangid The activity being migrated
     * @param int $versionid The freshly created version to attach the media to
     * @return object {mediafiles: int, posterfiles: int} Counts of files copied
     */
    public function migrate(int $elangid, int $versionid): object {
        global $DB;

        $result = (object) ['mediafiles' => 0, 'posterfiles' => 0];

        $cm = get_coursemodule_from_instance('elang', $elangid, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return $result;
        }

        $context = \context_module::instance($cm->id);
        $fs = get_file_storage();

        $result->mediafiles = $this->copy_area($fs, $context->id, 'videos', 'media', $versionid);
        $result->posterfiles = $this->copy_area($fs, $context->id, 'poster', 'poster', $versionid);

        if ($result->mediafiles > 0) {
            $DB->set_field('elang_version', 'mediakind', 'file', ['id' => $versionid]);
        }

        return $result;
    }

    /**
     * Copy every non-directory file from a version 1 area (itemid 0) into a
     * version 2 versioned area (itemid = versionid), preserving path and name.
     *
     * @param \file_storage $fs The file storage
     * @param int $contextid The module context id (same for source and target)
     * @param string $fromarea The version 1 source file area
     * @param string $toarea The version 2 target file area
     * @param int $versionid The target itemid
     * @return int The number of files copied
     */
    private function copy_area(
        \file_storage $fs,
        int $contextid,
        string $fromarea,
        string $toarea,
        int $versionid
    ): int {
        $count = 0;
        $files = $fs->get_area_files($contextid, 'mod_elang', $fromarea, self::V1_ITEMID, 'id', false);
        foreach ($files as $file) {
            $fs->create_file_from_storedfile([
                'contextid' => $contextid,
                'component' => 'mod_elang',
                'filearea' => $toarea,
                'itemid' => $versionid,
                'filepath' => $file->get_filepath(),
                'filename' => $file->get_filename(),
            ], $file);
            $count++;
        }

        return $count;
    }
}
