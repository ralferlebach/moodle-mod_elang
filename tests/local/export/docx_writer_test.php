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
 * Tests for the dependency-free DOCX writer: the output is a valid OOXML
 * container whose document part carries the title and paragraphs.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\local\export\docx_writer
 */
final class docx_writer_test extends \advanced_testcase {
    /**
     * Extract a named entry from the built archive.
     *
     * @param string $bytes The archive bytes
     * @param string $entry The entry path
     * @return string The entry content
     */
    private function entry(string $bytes, string $entry): string {
        $tempfile = make_request_directory() . '/read.docx';
        file_put_contents($tempfile, $bytes);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tempfile) === true);
        $content = $zip->getFromName($entry);
        $zip->close();
        $this->assertNotFalse($content, "Missing entry: $entry");
        return $content;
    }

    /**
     * The archive contains the required OOXML parts.
     *
     * @return void
     */
    public function test_contains_required_ooxml_parts(): void {
        $this->resetAfterTest();

        $bytes = (new docx_writer())->build('Title', ['Body.']);

        $parts = [
            '[Content_Types].xml',
            '_rels/.rels',
            'word/document.xml',
            'word/_rels/document.xml.rels',
            'word/styles.xml',
        ];
        foreach ($parts as $part) {
            $this->entry($bytes, $part);
        }
    }

    /**
     * The document part renders the title as Heading 1 and each paragraph as a
     * text run, with characters XML-escaped.
     *
     * @return void
     */
    public function test_document_carries_title_and_escaped_paragraphs(): void {
        $this->resetAfterTest();

        $bytes = (new docx_writer())->build('My <Title>', ['A & B', 'Second']);
        $document = $this->entry($bytes, 'word/document.xml');

        $this->assertStringContainsString('w:val="Heading1"', $document);
        $this->assertStringContainsString('My &lt;Title&gt;', $document);
        $this->assertStringContainsString('A &amp; B', $document);
        $this->assertStringContainsString('Second', $document);
        // The raw characters must not leak into the XML.
        $this->assertStringNotContainsString('<Title>', $document);
    }

    /**
     * An empty transcript still produces a valid document with just the title.
     *
     * @return void
     */
    public function test_empty_transcript_produces_title_only_document(): void {
        $this->resetAfterTest();

        $bytes = (new docx_writer())->build('Only title', []);
        $document = $this->entry($bytes, 'word/document.xml');

        $this->assertStringContainsString('Only title', $document);
        $this->assertSame(1, substr_count($document, '<w:p>'));
    }
}
