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
 * Golden-master tests for v1_cue_parser, against the real sample activity
 * (see tests/fixtures/v1_legacy_schema.php and Migration_V1_V2.md chapter
 * 1.1) rather than hand-invented JSON — every expected transcript and gap
 * below was computed independently from the same source (example.srt +
 * the elang_cues.json the client's V1 instance actually produced from it),
 * not copied from v1_cue_parser's own output.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\migration\v1_cue_parser
 */
final class v1_cue_parser_test extends \basic_testcase {
    /**
     * Cue 10: a single gap with help allowed, no link — the `[Example]`
     * bracket from example.srt.
     *
     * @return void
     */
    public function test_cue_with_a_single_help_allowed_gap(): void {
        $json = '[{"type":"text","content":"Welcome to the "},'
            . '{"type":"input","content":"Example","order":0,"help":true},'
            . '{"type":"text","content":" Subtitle File!"}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame('Welcome to the Example Subtitle File!', $result->transcript);
        $this->assertCount(1, $result->gaps);
        $this->assertSame(15, $result->gaps[0]->charstart);
        $this->assertSame(7, $result->gaps[0]->charlength);
        $this->assertSame('Example', $result->gaps[0]->solution);
        $this->assertSame('', $result->gaps[0]->linkurl);
        $this->assertTrue($result->gaps[0]->hintsallowed);
    }

    /**
     * Cue 11: a single gap with help NOT allowed — the `{demonstration}`
     * curly-brace form from example.srt. Confirms the [] vs {} distinction
     * survives as far as elang_cues.json is concerned: curly braces produce
     * "help":false, square brackets "help":true.
     *
     * @return void
     */
    public function test_cue_with_a_single_help_disallowed_gap(): void {
        $json = '[{"type":"text","content":"This is a "},'
            . '{"type":"input","content":"demonstration","order":1,"help":false},'
            . '{"type":"text","content":" of SRT subtitles."}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame('This is a demonstration of SRT subtitles.', $result->transcript);
        $this->assertCount(1, $result->gaps);
        $this->assertSame(10, $result->gaps[0]->charstart);
        $this->assertSame(13, $result->gaps[0]->charlength);
        $this->assertSame('demonstration', $result->gaps[0]->solution);
        $this->assertFalse($result->gaps[0]->hintsallowed);
    }

    /**
     * Cue 12: a gap with a link — the `[files (https://...)]` form. The
     * link's escaped forward slashes (`\/`) in the stored JSON must decode
     * to plain `/`, not survive as literal backslash-slash pairs.
     *
     * @return void
     */
    public function test_cue_with_a_link(): void {
        $json = '[{"type":"text","content":"You can use SRT "},'
            . '{"type":"input","content":"files","order":2,"help":true,'
            . '"link":"https:\/\/de.wikipedia.org\/wiki\/File"},'
            . '{"type":"text","content":" to add subtitles to your videos."}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame('You can use SRT files to add subtitles to your videos.', $result->transcript);
        $this->assertCount(1, $result->gaps);
        $this->assertSame(16, $result->gaps[0]->charstart);
        $this->assertSame(5, $result->gaps[0]->charlength);
        $this->assertSame('files', $result->gaps[0]->solution);
        $this->assertSame('https://de.wikipedia.org/wiki/File', $result->gaps[0]->linkurl);
    }

    /**
     * Cue 13: the transcript spans an embedded newline (example.srt's cue 4
     * is two source lines) — the newline must be preserved in the
     * reconstructed transcript and correctly included in the character
     * count on either side of it, not collapsed or miscounted.
     *
     * @return void
     */
    public function test_cue_with_an_embedded_newline(): void {
        $json = '[{"type":"text","content":"Each subtitle "},'
            . '{"type":"input","content":"entry","order":3,"help":false},'
            . '{"type":"text","content":" consists of a number, a timecode,\nand the subtitle text."}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame(
            "Each subtitle entry consists of a number, a timecode,\nand the subtitle text.",
            $result->transcript
        );
        $this->assertSame(14, $result->gaps[0]->charstart);
        $this->assertSame(5, $result->gaps[0]->charlength);
    }

    /**
     * Cue 14: two gaps in the same cue — both charstart values must be
     * measured against the accumulating transcript (i.e. the second gap's
     * position accounts for the first gap's solution text already being
     * "typed out" ahead of it), not against the cue in isolation.
     *
     * @return void
     */
    public function test_cue_with_two_gaps(): void {
        $json = '[{"type":"text","content":"The "},'
            . '{"type":"input","content":"timecode","order":4,"help":true},'
            . '{"type":"text","content":" "},'
            . '{"type":"input","content":"format","order":5,"help":false},'
            . '{"type":"text","content":" is hours:minutes:seconds,milliseconds."}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame('The timecode format is hours:minutes:seconds,milliseconds.', $result->transcript);
        $this->assertCount(2, $result->gaps);

        $this->assertSame(4, $result->gaps[0]->charstart);
        $this->assertSame(8, $result->gaps[0]->charlength);
        $this->assertSame('timecode', $result->gaps[0]->solution);

        $this->assertSame(13, $result->gaps[1]->charstart);
        $this->assertSame(6, $result->gaps[1]->charlength);
        $this->assertSame('format', $result->gaps[1]->solution);
    }

    /**
     * Cue 17: a cue with no gaps at all is valid — an empty gaps array, full
     * transcript preserved, including its apostrophe.
     *
     * @return void
     */
    public function test_cue_with_no_gaps(): void {
        $json = '[{"type":"text","content":"And that\'s how you create an SRT subtitle file!"}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame("And that's how you create an SRT subtitle file!", $result->transcript);
        $this->assertSame([], $result->gaps);
    }

    /**
     * Cue 18: the last cue of the sample activity, included to round out
     * coverage of every distinct shape in the real dataset (single trailing
     * gap, no link, help disallowed).
     *
     * @return void
     */
    public function test_cue_with_a_trailing_gap(): void {
        $json = '[{"type":"text","content":"Enjoy adding "},'
            . '{"type":"input","content":"subtitles","order":8,"help":false},'
            . '{"type":"text","content":" to your videos!"}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame('Enjoy adding subtitles to your videos!', $result->transcript);
        $this->assertSame(13, $result->gaps[0]->charstart);
        $this->assertSame(9, $result->gaps[0]->charlength);
        $this->assertSame('subtitles', $result->gaps[0]->solution);
        $this->assertFalse($result->gaps[0]->hintsallowed);
    }

    /**
     * Malformed json (not a JSON array at all) fails loudly rather than
     * silently producing an empty or partial transcript.
     *
     * @return void
     */
    public function test_rejects_json_that_is_not_an_array(): void {
        $this->expectException(\moodle_exception::class);
        v1_cue_parser::parse('{"not":"an array"}');
    }

    /**
     * A gap's `order` field is deliberately never read by the parser: real
     * V1 output (see the fixture and Migration_V1_V2.md chapter 3.1) has
     * duplicate and missing `order` values because of the V1 gap-counter
     * bug, so nothing here may depend on it. Character position is derived
     * purely from segment sequence.
     *
     * @return void
     */
    public function test_gap_order_field_is_ignored(): void {
        $json = '[{"type":"text","content":"a "},'
            . '{"type":"input","content":"b","order":999,"help":false},'
            . '{"type":"text","content":" c"}]';

        $result = v1_cue_parser::parse($json);

        $this->assertSame('a b c', $result->transcript);
        $this->assertSame(2, $result->gaps[0]->charstart);
        $this->assertSame('b', $result->gaps[0]->solution);
    }
}
