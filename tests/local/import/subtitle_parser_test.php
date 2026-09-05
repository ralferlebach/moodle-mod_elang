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

    /**
     * Content larger than the ceiling is refused before it is parsed.
     *
     * The split on blank lines allocates a copy of the whole input, so the size
     * check has to come first — refusing afterwards would already have paid the
     * cost the refusal exists to avoid.
     *
     * @return void
     */
    public function test_oversized_content_is_refused(): void {
        $huge = str_repeat("WEBVTT\n\n00:00:00.000 --> 00:00:01.000\nx\n\n", 60000);
        $this->assertGreaterThan(subtitle_parser::MAX_CONTENT_BYTES, strlen($huge));

        $this->expectException(\moodle_exception::class);
        (new subtitle_parser())->parse($huge);
    }

    /**
     * Content that is not UTF-8 is refused with an explanation.
     *
     * A file saved in a legacy encoding is the ordinary cause. Left through, the
     * broken bytes reach the database and surface later as a transcript nobody
     * can account for.
     *
     * @return void
     */
    public function test_non_utf8_content_is_refused(): void {
        // The accent byte of a Latin-1 file, which is not valid UTF-8.
        $latin1 = "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\nLe ch\xE2teau dort\n";
        $this->assertFalse(mb_check_encoding($latin1, 'UTF-8'));

        $this->expectException(\moodle_exception::class);
        (new subtitle_parser())->parse($latin1);
    }

    /**
     * Valid UTF-8 with non-ASCII characters is not caught by that check.
     *
     * @return void
     */
    public function test_utf8_accents_are_accepted(): void {
        $content = "WEBVTT\n\n00:00:00.000 --> 00:00:01.000\nLe château dort\n";

        $parsed = (new subtitle_parser())->parse($content);

        $this->assertCount(1, $parsed->cues);
        $this->assertSame('Le château dort', $parsed->cues[0]->transcript);
    }

    /**
     * A block containing an absurdly long line is skipped with a warning
     * rather than failing the whole import.
     *
     * One minified file or corrupted download among the blocks should cost that
     * block, not the transcript around it.
     *
     * @return void
     */
    public function test_an_overlong_line_skips_only_its_block(): void {
        $long = str_repeat('a', subtitle_parser::MAX_LINE_LENGTH + 1);
        $content = "WEBVTT\n\n"
            . "00:00:00.000 --> 00:00:01.000\nLe chat dort\n\n"
            . "00:00:01.000 --> 00:00:02.000\n" . $long . "\n\n"
            . "00:00:02.000 --> 00:00:03.000\nLe chien court\n";

        $parsed = (new subtitle_parser())->parse($content);

        $this->assertCount(2, $parsed->cues);
        $this->assertSame('Le chat dort', $parsed->cues[0]->transcript);
        $this->assertSame('Le chien court', $parsed->cues[1]->transcript);
        $this->assertNotEmpty($parsed->warnings);
    }

    /**
     * More cues than the ceiling are refused rather than truncated.
     *
     * Keeping the first few thousand would hand back an exercise missing its
     * ending, and the author would have no way to tell.
     *
     * @return void
     */
    public function test_too_many_cues_are_refused_not_truncated(): void {
        $blocks = ["WEBVTT\n"];
        for ($i = 0; $i <= subtitle_parser::MAX_CUES; $i++) {
            $start = sprintf('00:00:%02d.%03d', intdiv($i, 1000) % 60, $i % 1000);
            $end = sprintf('00:00:%02d.%03d', intdiv($i + 1, 1000) % 60, ($i + 1) % 1000);
            $blocks[] = "$start --> $end\nLine $i";
        }

        $this->expectException(\moodle_exception::class);
        (new subtitle_parser())->parse(implode("\n\n", $blocks));
    }

    /**
     * Content that yields no cues is refused rather than returned empty.
     *
     * An empty result let the import modal offer to apply zero cues: the author
     * pressed "check", read "0 cues found", and could import nothing at all.
     * There was no error anywhere and no way to tell that the file had simply
     * not been understood.
     *
     * @dataProvider unreadable_content_provider
     * @param string $content The content a person might paste or upload
     * @return void
     */
    public function test_content_yielding_no_cues_is_refused(string $content): void {
        $this->expectException(\moodle_exception::class);
        (new subtitle_parser())->parse($content);
    }

    /**
     * Things that are not subtitle files.
     *
     * @return array The content
     */
    public static function unreadable_content_provider(): array {
        return [
            'prose' => ['this is not a subtitle file'],
            'only a header' => ["WEBVTT\n"],
            'text without timings' => ["Le chat dort\n\nLe chien court\n"],
            'whitespace' => ["   \n\n  \n"],
        ];
    }

    /**
     * A file whose only block is skipped for a long line is refused too.
     *
     * The skip warning alone would leave an author looking at an empty summary
     * with a warning they could easily miss.
     *
     * @return void
     */
    public function test_a_file_whose_every_block_is_skipped_is_refused(): void {
        $long = str_repeat('a', subtitle_parser::MAX_LINE_LENGTH + 1);

        $this->expectException(\moodle_exception::class);
        (new subtitle_parser())->parse("WEBVTT\n\n00:00:00.000 --> 00:00:01.000\n" . $long . "\n");
    }
}
