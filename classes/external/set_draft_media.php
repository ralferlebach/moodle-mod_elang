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
use core_external\external_single_structure;
use core_external\external_value;
use mod_elang\local\media\provider_registry;

/**
 * Set a draft version's medium: an uploaded file, a url, a provider, or none.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class set_draft_media extends external_api {
    use authoring_helper;

    /**
     * Describe the parameters this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version to set the medium on'),
            'kind' => new external_value(PARAM_ALPHA, 'file, url, provider, or empty for no medium'),
            'url' => new external_value(PARAM_URL, 'Direct media URL when kind is url', VALUE_DEFAULT, ''),
            'provider' => new external_value(PARAM_ALPHANUMEXT, 'Provider name when kind is provider', VALUE_DEFAULT, ''),
            'providerref' => new external_value(PARAM_RAW, 'Provider-specific reference when kind is provider', VALUE_DEFAULT, ''),
            'mime' => new external_value(PARAM_NOTAGS, 'Optional MIME type hint for the medium', VALUE_DEFAULT, ''),
            'duration' => new external_value(PARAM_INT, 'Optional media duration in whole seconds, 0 if unknown', VALUE_DEFAULT, 0),
            'mediadraftitemid' => new external_value(
                PARAM_INT,
                'Draft file area id holding the uploaded media file when kind is file',
                VALUE_DEFAULT,
                0
            ),
            'posterdraftitemid' => new external_value(
                PARAM_INT,
                'Draft file area id holding the uploaded poster image when kind is file',
                VALUE_DEFAULT,
                0
            ),
        ]);
    }

    /**
     * Set the draft's medium and return the resulting media descriptor.
     *
     * @param int $versionid Id of the draft version
     * @param string $kind file, url, provider, or empty for no medium
     * @param string $url Direct media URL when kind is url
     * @param string $provider Provider name when kind is provider
     * @param string $providerref Provider-specific reference when kind is provider
     * @param string $mime Optional MIME type hint
     * @param int $duration Optional media duration in whole seconds
     * @param int $mediadraftitemid Draft area with the uploaded media file when kind is file
     * @param int $posterdraftitemid Draft area with the uploaded poster image when kind is file
     * @return array The resulting media descriptor, see execute_returns()
     */
    public static function execute(
        int $versionid,
        string $kind,
        string $url = '',
        string $provider = '',
        string $providerref = '',
        string $mime = '',
        int $duration = 0,
        int $mediadraftitemid = 0,
        int $posterdraftitemid = 0
    ): array {
        [
            'versionid' => $versionid,
            'kind' => $kind,
            'url' => $url,
            'provider' => $provider,
            'providerref' => $providerref,
            'mime' => $mime,
            'duration' => $duration,
            'mediadraftitemid' => $mediadraftitemid,
            'posterdraftitemid' => $posterdraftitemid,
        ] = self::validate_parameters(self::execute_parameters(), [
            'versionid' => $versionid,
            'kind' => $kind,
            'url' => $url,
            'provider' => $provider,
            'providerref' => $providerref,
            'mime' => $mime,
            'duration' => $duration,
            'mediadraftitemid' => $mediadraftitemid,
            'posterdraftitemid' => $posterdraftitemid,
        ]);

        self::require_manage_version($versionid);

        if ($kind === 'provider') {
            if (!provider_registry::is_known($provider)) {
                throw new \moodle_exception('error:unknownmediaprovider', 'mod_elang', '', $provider);
            }
            $normalised = provider_registry::normalise_reference($provider, $providerref);
            if ($normalised === null) {
                throw new \moodle_exception('error:invalidproviderref', 'mod_elang', '', $providerref);
            }
            $providerref = $normalised;
        }

        $version = self::get_version_manager()->set_draft_media($versionid, [
            'kind' => $kind,
            'url' => $url,
            'provider' => $provider,
            'providerref' => $providerref,
            'mime' => $mime,
            'duration' => $duration,
            'mediadraftitemid' => $mediadraftitemid,
            'posterdraftitemid' => $posterdraftitemid,
        ]);

        return [
            'versionid' => (int) $version->id,
            'mediakind' => (string) ($version->mediakind ?? ''),
            'mediaurl' => (string) ($version->mediaurl ?? ''),
            'mediaprovider' => (string) ($version->mediaprovider ?? ''),
            'mediaproviderref' => (string) ($version->mediaproviderref ?? ''),
            'mediamime' => (string) ($version->mediamime ?? ''),
            'mediaduration' => $version->mediaduration !== null ? (int) $version->mediaduration : 0,
        ];
    }

    /**
     * Describe the structure this function returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'versionid' => new external_value(PARAM_INT, 'Id of the draft version'),
            'mediakind' => new external_value(PARAM_ALPHA, 'file, url, provider, or empty for no medium'),
            'mediaurl' => new external_value(PARAM_RAW, 'Direct media URL, empty unless kind is url'),
            'mediaprovider' => new external_value(PARAM_RAW, 'Provider name, empty unless kind is provider'),
            'mediaproviderref' => new external_value(PARAM_RAW, 'Provider reference, empty unless kind is provider'),
            'mediamime' => new external_value(PARAM_RAW, 'MIME type hint, empty if unset'),
            'mediaduration' => new external_value(PARAM_INT, 'Media duration in whole seconds, 0 if unknown'),
        ]);
    }
}
