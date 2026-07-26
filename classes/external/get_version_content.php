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
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Read a version's full authoring content — including solutions — for the editor.
 *
 * This is the manager-facing counterpart to get_attempt_cues: where the player
 * receives a solution-masked, attempt-bound view, the editor receives the
 * complete content it can edit and send back through save_draft_version. Access
 * is gated on mod/elang:manage, so solutions never reach a learner.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_version_content extends external_api {
    use authoring_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the version to read'),
        ]);
    }

    /**
     * Return a version's metadata and full content.
     *
     * @param int $versionid Id of the version to read
     * @return array The version metadata and its cues, see execute_returns()
     */
    public static function execute(int $versionid): array {
        ['versionid' => $versionid] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
        ]);

        [$version] = self::require_manage_version($versionid);

        return [
            'versionid' => (int) $version->id,
            'versionnumber' => (int) $version->versionnumber,
            'status' => $version->status,
            'revision' => (int) $version->revision,
            'language' => (string) $version->language,
            'jarothreshold' => (float) $version->jarothreshold,
            'mediakind' => (string) ($version->mediakind ?? ''),
            'mediaurl' => (string) ($version->mediaurl ?? ''),
            'mediaprovider' => (string) ($version->mediaprovider ?? ''),
            'mediaproviderref' => (string) ($version->mediaproviderref ?? ''),
            'mediamime' => (string) ($version->mediamime ?? ''),
            'mediaduration' => $version->mediaduration !== null ? (int) $version->mediaduration : 0,
            'cues' => self::get_version_manager()->load_version_content($versionid),
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'versionid' => new external_value(PARAM_INT, 'Id of the version'),
            'versionnumber' => new external_value(PARAM_INT, 'Sequential version number within the activity'),
            'status' => new external_value(PARAM_ALPHA, 'draft, published or archived'),
            'revision' => new external_value(PARAM_INT, 'Per-version content revision counter'),
            'language' => new external_value(PARAM_RAW, 'Content language/script code used for grading'),
            'jarothreshold' => new external_value(PARAM_FLOAT, 'Jaro similarity threshold used by the wordrecognized algorithm'),
            'mediakind' => new external_value(PARAM_ALPHA, 'file, url or provider, empty when the version has no medium'),
            'mediaurl' => new external_value(PARAM_RAW, 'Direct media URL when mediakind is url, empty otherwise'),
            'mediaprovider' => new external_value(PARAM_RAW, 'External provider name when mediakind is provider, empty otherwise'),
            'mediaproviderref' => new external_value(PARAM_RAW, 'Provider-specific reference, empty otherwise'),
            'mediamime' => new external_value(PARAM_RAW, 'Optional MIME type hint for the medium, empty if unknown'),
            'mediaduration' => new external_value(PARAM_INT, 'Optional media duration in whole seconds, 0 if unknown'),
            'cues' => new external_multiple_structure(self::cue_structure(), 'The version\'s cues, with solutions'),
        ]);
    }
}
