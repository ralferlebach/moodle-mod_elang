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

namespace mod_elang\local\import;

/**
 * Lenient parser for WebVTT and SubRip (.srt) subtitle files.
 *
 * A single pass handles both formats: files are split into blocks on blank
 * lines, and within each block the line containing "-->" is the timing line;
 * anything before it (a SubRip index number, a WebVTT cue identifier) is
 * ignored, and the lines after it are the transcript. Blocks without a timing
 * line (the WEBVTT header, NOTE/STYLE/REGION sections) are skipped. Timestamps
 * are accepted with either a comma (SubRip) or a dot (WebVTT) before the
 * milliseconds, and with the hours component optional (MM:SS.mmm). Parsing
 * never touches the database — it turns file text into cue segments the
 * authoring editor previews and, once the teacher is happy, saves.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class subtitle_parser {
    /**
     * The largest subtitle content this parser will look at, in bytes.
     *
     * A subtitle file for a lesson recording is a few tens of kilobytes; two
     * megabytes is a generous ceiling for anything that is genuinely a
     * transcript. Above it the input is not a subtitle file, and the parser
     * should say so rather than spend a request finding out — the split on
     * blank lines alone allocates a copy of the whole content.
     *
     * Enforced here rather than at the web service, because the editor, the
     * import modal and any later caller all arrive through this one method.
     */
    public const MAX_CONTENT_BYTES = 2 * 1024 * 1024;

    /**
     * The largest number of cues one import may produce.
     *
     * Four thousand is roughly a full day of continuous speech. Beyond it the
     * result would be an exercise nobody could work through, and the editor
     * would have to render every one of them.
     */
    public const MAX_CUES = 4000;

    /**
     * The longest single line the parser accepts, in characters.
     *
     * A subtitle line is a sentence. A single line of hundreds of kilobytes is
     * a minified file, a data URL or a corrupted download, and treating it as
     * a transcript produces one unusable cue rather than an error.
     */
    public const MAX_LINE_LENGTH = 5000;

    /**
     * Parse subtitle file content into ordered cue segments.
     *
     * @param string $content The raw subtitle file content
     * @return object An object with a `cues` array (each with sortorder,
     *         starttime, endtime and transcript) and a `warnings` string array
     *         listing the non-fatal problems that caused a block to be skipped
     */
    public function parse(string $content): object {
        $warnings = [];
        $cues = [];

        // Refused before anything is allocated. The checks below are ordered by
        // what they cost: length first, then encoding, then the split.
        if (strlen($content) > self::MAX_CONTENT_BYTES) {
            throw new \moodle_exception('error_importtoolarge', 'mod_elang', '', (object) [
                'size' => display_size(strlen($content)),
                'max' => display_size(self::MAX_CONTENT_BYTES),
            ]);
        }

        // Invalid UTF-8 would otherwise travel all the way into the database
        // and surface later as a broken transcript nobody can explain. A file
        // saved in a legacy encoding is the ordinary cause, and the message
        // says so instead of blaming the content.
        if (!mb_check_encoding($content, 'UTF-8')) {
            throw new \moodle_exception('error_importnotutf8', 'mod_elang');
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);

        $blocks = preg_split('/\n[ \t]*\n/', trim($content));

        $sortorder = 0;
        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            $lines = explode("\n", $block);

            // One absurdly long line is a minified file or a corrupted
            // download, not a sentence. Skipped with a warning rather than
            // refused outright, so one bad block does not cost the whole
            // import.
            $longest = 0;
            foreach ($lines as $line) {
                $longest = max($longest, \core_text::strlen($line));
            }
            if ($longest > self::MAX_LINE_LENGTH) {
                $warnings[] = get_string('import_warnlinetoolong', 'mod_elang', (object) [
                    'block' => $sortorder + 1,
                    'max' => self::MAX_LINE_LENGTH,
                ]);
                continue;
            }

            $timingindex = null;
            foreach ($lines as $index => $line) {
                if (strpos($line, '-->') !== false) {
                    $timingindex = $index;
                    break;
                }
            }

            if ($timingindex === null) {
                // A header (WEBVTT) or a NOTE/STYLE/REGION section: not a cue.
                continue;
            }

            $timing = $this->parse_timing_line($lines[$timingindex]);
            if ($timing === null) {
                $warnings[] = get_string('import_badtiming', 'mod_elang', trim($lines[$timingindex]));
                continue;
            }

            $transcript = trim(implode("\n", array_slice($lines, $timingindex + 1)));
            if ($transcript === '') {
                $warnings[] = get_string('import_emptytranscript', 'mod_elang');
                continue;
            }

            $sortorder++;
            $cues[] = (object) [
                'sortorder' => $sortorder,
                'starttime' => $timing[0],
                'endtime' => $timing[1],
                'transcript' => $transcript,
            ];
        }

        // Content that yields nothing is not an empty subtitle file, it is not a
        // subtitle file. Returning an empty result let the import modal offer to
        // apply zero cues: the author pressed "check", saw "0 cues found", and
        // could then import nothing at all — no error anywhere, and no way to
        // tell that the file had simply not been understood.
        if (empty($cues)) {
            throw new \moodle_exception('error_importnocues', 'mod_elang');
        }

        // Refused rather than truncated: silently keeping the first four
        // thousand would hand back an exercise missing its ending, and the
        // author would have no way to tell.
        if (count($cues) > self::MAX_CUES) {
            throw new \moodle_exception('error_importtoomanycues', 'mod_elang', '', (object) [
                'count' => count($cues),
                'max' => self::MAX_CUES,
            ]);
        }

        return (object) ['cues' => $cues, 'warnings' => $warnings];
    }

    /**
     * Extract the start and end milliseconds from a timing line such as
     * "00:00:01.000 --> 00:00:04.000 line:0 position:20%". Any cue settings
     * after the end timestamp are ignored.
     *
     * @param string $line The timing line
     * @return array|null A two-element [start, end] list in milliseconds, or null if unparseable
     */
    private function parse_timing_line(string $line): ?array {
        if (!preg_match('/([0-9:.,]+)\s*-->\s*([0-9:.,]+)/', $line, $matches)) {
            return null;
        }

        $start = $this->timestamp_to_ms($matches[1]);
        $end = $this->timestamp_to_ms($matches[2]);
        if ($start === null || $end === null) {
            return null;
        }

        return [$start, $end];
    }

    /**
     * Convert a single timestamp (HH:MM:SS.mmm, MM:SS.mmm, comma or dot before
     * the milliseconds) into whole milliseconds.
     *
     * @param string $timestamp The timestamp to convert
     * @return int|null The time in milliseconds, or null if the format is not recognised
     */
    private function timestamp_to_ms(string $timestamp): ?int {
        $timestamp = str_replace(',', '.', trim($timestamp));
        if (!preg_match('/^(?:(\d+):)?(\d{1,2}):(\d{1,2})(?:\.(\d{1,3}))?$/', $timestamp, $matches)) {
            return null;
        }

        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) $matches[2];
        $seconds = (int) $matches[3];
        $millis = (int) str_pad($matches[4] ?? '0', 3, '0', STR_PAD_RIGHT);

        return ((($hours * 60 + $minutes) * 60) + $seconds) * 1000 + $millis;
    }
}
