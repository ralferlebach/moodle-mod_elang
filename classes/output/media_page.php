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

namespace mod_elang\output;

use context_module;
use moodle_url;
use renderable;
use renderer_base;
use stdClass;
use templatable;

/**
 * The media page: choose a medium on the left, see the current one on the right.
 *
 * Subtitles are timed against a medium, so this is the first thing an author
 * needs and the page it makes sense to land on. Showing what is currently set
 * next to the form matters more here than on most upload pages: replacing the
 * medium of an activity that already has cues is a decision, and it should not
 * be made without seeing what is being replaced.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class media_page implements renderable, templatable {
    /** @var stdClass The draft version record */
    private stdClass $draft;

    /** @var context_module The module context */
    private context_module $context;

    /** @var string The rendered media form */
    private string $formhtml;

    /** @var bool Whether the activity already has cues that a new medium would outlive */
    private bool $hascues;

    /**
     * Build the page description.
     *
     * @param stdClass $draft The draft version record
     * @param context_module $context The module context
     * @param string $formhtml The rendered media form
     * @param bool $hascues Whether the draft already carries cues
     */
    public function __construct(stdClass $draft, context_module $context, string $formhtml, bool $hascues) {
        $this->draft = $draft;
        $this->context = $context;
        $this->formhtml = $formhtml;
        $this->hascues = $hascues;
    }

    /**
     * Describe the medium currently set on the draft.
     *
     * @return array The medium, or an empty array when none is set
     */
    private function current_medium(): array {
        $kind = (string) ($this->draft->mediakind ?? '');
        if ($kind === '') {
            return [];
        }

        $medium = [
            'kind' => $kind,
            'isfile' => $kind === 'file',
            'isurl' => $kind === 'url',
            'isprovider' => $kind === 'provider',
            'url' => '',
            'filename' => '',
            'meta' => '',
            'posterurl' => '',
            'isaudio' => false,
        ];

        $mime = (string) ($this->draft->mediamime ?? '');
        $parts = [];

        if ($kind === 'file') {
            $files = get_file_storage()->get_area_files(
                $this->context->id,
                'mod_elang',
                'media',
                (int) $this->draft->id,
                'filepath, filename',
                false
            );
            $file = reset($files);
            if ($file === false) {
                return [];
            }

            $medium['url'] = moodle_url::make_pluginfile_url(
                $this->context->id,
                'mod_elang',
                'media',
                (int) $this->draft->id,
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
            $medium['filename'] = $file->get_filename();
            $mime = $mime !== '' ? $mime : (string) $file->get_mimetype();
            $parts[] = display_size($file->get_filesize());
        } else if ($kind === 'url') {
            $medium['url'] = (string) ($this->draft->mediaurl ?? '');
            $medium['filename'] = $medium['url'];
        } else {
            $medium['filename'] = get_string(
                'provider_' . (string) ($this->draft->mediaprovider ?? ''),
                'mod_elang'
            );
            $parts[] = (string) ($this->draft->mediaproviderref ?? '');
        }

        $medium['isaudio'] = strpos($mime, 'audio/') === 0;
        if ($mime !== '') {
            array_unshift($parts, $mime);
        }

        $duration = (int) ($this->draft->mediaduration ?? 0);
        if ($duration > 0) {
            $parts[] = format_time($duration);
        }

        $medium['meta'] = implode(' · ', array_filter($parts));

        $posterfiles = get_file_storage()->get_area_files(
            $this->context->id,
            'mod_elang',
            'poster',
            (int) $this->draft->id,
            'filepath, filename',
            false
        );
        $poster = reset($posterfiles);
        if ($poster !== false) {
            $medium['posterurl'] = moodle_url::make_pluginfile_url(
                $this->context->id,
                'mod_elang',
                'poster',
                (int) $this->draft->id,
                $poster->get_filepath(),
                $poster->get_filename()
            )->out(false);
        }

        return $medium;
    }

    /**
     * Export the page for the template.
     *
     * @param renderer_base $output The renderer
     * @return array The template context
     */
    public function export_for_template(renderer_base $output): array {
        $medium = $this->current_medium();

        return [
            'heading' => get_string('media_heading', 'mod_elang'),
            'intro' => get_string('media_intro', 'mod_elang'),
            'formhtml' => $this->formhtml,
            'currenttitle' => get_string('media_current', 'mod_elang'),
            'hasmedium' => !empty($medium),
            'medium' => $medium,
            'nomedium' => get_string('media_none', 'mod_elang'),
            // The warning is only worth showing to somebody who has something
            // to lose: an activity with no cues yet cannot break them.
            'showcuenote' => $this->hascues,
            'cuenote' => get_string('media_cuenote', 'mod_elang'),
        ];
    }
}
