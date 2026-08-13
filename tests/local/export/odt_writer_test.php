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

namespace mod_elang\local\export;

/**
 * Tests for the dependency-free ODT writer: a valid OpenDocument container
 * whose mimetype part comes first and is stored uncompressed, and whose
 * content carries the title and paragraphs.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\export\odt_writer
 */
final class odt_writer_test extends \advanced_testcase {
    /**
     * Open the built archive from its bytes.
     *
     * @param string $bytes The archive bytes
     * @return \ZipArchive The opened archive
     */
    private function open(string $bytes): \ZipArchive {
        $tempfile = make_request_directory() . '/read.odt';
        file_put_contents($tempfile, $bytes);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempfile) === true);
        return $zip;
    }

    /**
     * The mimetype part is the first entry and is stored uncompressed, so a
     * reader can sniff the format from the archive's first bytes.
     *
     * @return void
     */
    public function test_mimetype_is_first_and_stored(): void {
        $this->resetAfterTest();

        $bytes = (new odt_writer())->build('Title', ['Body.']);
        $zip = $this->open($bytes);

        $first = $zip->statIndex(0);
        $this->assertSame('mimetype', $first['name']);
        // CM_STORE means compressed size equals the raw size.
        $this->assertSame($first['size'], $first['comp_size']);
        $this->assertSame('application/vnd.oasis.opendocument.text', $zip->getFromName('mimetype'));
        $zip->close();
    }

    /**
     * The archive contains the required OpenDocument parts.
     *
     * @return void
     */
    public function test_contains_required_odf_parts(): void {
        $this->resetAfterTest();

        $bytes = (new odt_writer())->build('Title', ['Body.']);
        $zip = $this->open($bytes);

        foreach (['mimetype', 'META-INF/manifest.xml', 'content.xml', 'styles.xml'] as $part) {
            $this->assertNotFalse($zip->getFromName($part), "Missing entry: $part");
        }
        $zip->close();
    }

    /**
     * The content part renders the title and each paragraph, with characters
     * XML-escaped.
     *
     * @return void
     */
    public function test_content_carries_title_and_escaped_paragraphs(): void {
        $this->resetAfterTest();

        $bytes = (new odt_writer())->build('My <Title>', ['A & B', 'Second']);
        $zip = $this->open($bytes);
        $content = $zip->getFromName('content.xml');
        $zip->close();

        $this->assertStringContainsString('text:style-name="Title"', $content);
        $this->assertStringContainsString('My &lt;Title&gt;', $content);
        $this->assertStringContainsString('A &amp; B', $content);
        $this->assertStringContainsString('Second', $content);
        $this->assertStringNotContainsString('<Title>', $content);
    }
}
