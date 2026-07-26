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
 * Tests for subtitle_parser.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\import\subtitle_parser
 */
final class subtitle_parser_test extends \advanced_testcase {
    /**
     * A WebVTT file, including a header and a multi-line cue, parses into
     * ordered cues with millisecond timings.
     *
     * @return void
     */
    public function test_parses_webvtt(): void {
        $vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nHello world\n\n"
            . "00:00:05.500 --> 00:00:08.000\nSecond cue\nline two\n";

        $result = (new subtitle_parser())->parse($vtt);

        $this->assertCount(2, $result->cues);
        $this->assertSame(1, $result->cues[0]->sortorder);
        $this->assertSame(1000, $result->cues[0]->starttime);
        $this->assertSame(4000, $result->cues[0]->endtime);
        $this->assertSame('Hello world', $result->cues[0]->transcript);
        $this->assertSame(2, $result->cues[1]->sortorder);
        $this->assertSame(5500, $result->cues[1]->starttime);
        $this->assertSame("Second cue\nline two", $result->cues[1]->transcript);
        $this->assertSame([], $result->warnings);
    }

    /**
     * A SubRip file, with index lines and comma-separated milliseconds, parses
     * the same way.
     *
     * @return void
     */
    public function test_parses_subrip(): void {
        $srt = "1\n00:00:01,000 --> 00:00:04,000\nHello world\n\n2\n00:00:05,000 --> 00:00:08,000\nSecond cue\n";

        $result = (new subtitle_parser())->parse($srt);

        $this->assertCount(2, $result->cues);
        $this->assertSame(1000, $result->cues[0]->starttime);
        $this->assertSame('Hello world', $result->cues[0]->transcript);
        $this->assertSame(8000, $result->cues[1]->endtime);
    }

    /**
     * Timestamps may omit the hours component (MM:SS.mmm).
     *
     * @return void
     */
    public function test_parses_timestamps_without_hours(): void {
        $result = (new subtitle_parser())->parse("WEBVTT\n\n01:02.500 --> 01:05.000\nText\n");

        $this->assertCount(1, $result->cues);
        $this->assertSame(62500, $result->cues[0]->starttime);
        $this->assertSame(65000, $result->cues[0]->endtime);
    }

    /**
     * Cue settings after the end timestamp are ignored.
     *
     * @return void
     */
    public function test_ignores_cue_settings_after_the_timestamp(): void {
        $result = (new subtitle_parser())->parse("WEBVTT\n\n00:00:01.000 --> 00:00:02.000 line:0 position:20%\nText\n");

        $this->assertCount(1, $result->cues);
        $this->assertSame(2000, $result->cues[0]->endtime);
    }

    /**
     * The WEBVTT header and NOTE sections are skipped, not turned into cues.
     *
     * @return void
     */
    public function test_skips_header_and_note_blocks(): void {
        $vtt = "WEBVTT - A title\n\nNOTE this is a comment\n\n00:00:01.000 --> 00:00:02.000\nText\n";

        $result = (new subtitle_parser())->parse($vtt);

        $this->assertCount(1, $result->cues);
        $this->assertSame('Text', $result->cues[0]->transcript);
    }

    /**
     * A block whose timing line cannot be parsed is skipped and reported.
     *
     * @return void
     */
    public function test_skips_a_block_with_unparseable_timing(): void {
        $vtt = "WEBVTT\n\nnot a timestamp --> also not\nText\n\n00:00:01.000 --> 00:00:02.000\nGood\n";

        $result = (new subtitle_parser())->parse($vtt);

        $this->assertCount(1, $result->cues);
        $this->assertSame('Good', $result->cues[0]->transcript);
        $this->assertNotEmpty($result->warnings);
    }

    /**
     * A cue with a timing line but no transcript text is skipped and reported.
     *
     * @return void
     */
    public function test_skips_a_cue_with_no_transcript_text(): void {
        $vtt = "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\n\n00:00:03.000 --> 00:00:04.000\nText\n";

        $result = (new subtitle_parser())->parse($vtt);

        $this->assertCount(1, $result->cues);
        $this->assertSame('Text', $result->cues[0]->transcript);
        $this->assertNotEmpty($result->warnings);
    }
}
