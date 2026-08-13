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
 * Builds a minimal, valid ODT (OpenDocument Text) document from a title and a
 * list of paragraphs, with no third-party library.
 *
 * An ODT is a ZIP with a specific shape: the mimetype part must come first and
 * be stored uncompressed, so a reader can identify the format from the file's
 * first bytes. That rule rules out Moodle's zip_packer (which compresses every
 * entry), so this writer uses PHP's ZipArchive directly — a core PHP
 * extension, not a bundled library — and stores the mimetype with CM_STORE
 * before adding the compressed parts.
 *
 * The title becomes a Heading paragraph, each transcript paragraph a normal
 * paragraph; the referenced styles are declared in the document's automatic
 * styles. Text is XML-escaped.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class odt_writer {
    /**
     * Build the ODT bytes for a title and paragraphs.
     *
     * @param string $title The document title, rendered as a heading
     * @param string[] $paragraphs The body paragraphs, in order
     * @return string The ODT file as a binary string
     */
    public function build(string $title, array $paragraphs): string {
        $tempfile = make_request_directory() . '/transcript.odt';

        $zip = new \ZipArchive();
        if ($zip->open($tempfile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return '';
        }

        // The mimetype part must be first and stored uncompressed.
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.text');
        $zip->setCompressionName('mimetype', \ZipArchive::CM_STORE);

        $zip->addFromString('META-INF/manifest.xml', $this->manifest());
        $zip->addFromString('content.xml', $this->content($title, $paragraphs));
        $zip->addFromString('styles.xml', $this->styles());
        $zip->close();

        $bytes = file_get_contents($tempfile);

        return $bytes === false ? '' : $bytes;
    }

    /**
     * The package manifest listing every part.
     *
     * @return string The META-INF/manifest.xml part
     */
    private function manifest(): string {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" '
            . 'manifest:version="1.2">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.2" '
            . 'manifest:media-type="application/vnd.oasis.opendocument.text"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '<manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>'
            . '</manifest:manifest>';
    }

    /**
     * The document content: a heading paragraph followed by one normal
     * paragraph per transcript paragraph.
     *
     * @param string $title The title text
     * @param string[] $paragraphs The body paragraphs
     * @return string The content.xml part
     */
    private function content(string $title, array $paragraphs): string {
        $body = $this->paragraph($title, 'Title');
        foreach ($paragraphs as $paragraph) {
            $body .= $this->paragraph($paragraph, 'Standard');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content '
            . 'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '
            . 'xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" '
            . 'office:version="1.2">'
            . '<office:body><office:text>' . $body . '</office:text></office:body>'
            . '</office:document-content>';
    }

    /**
     * Render one paragraph in a named style.
     *
     * @param string $text The paragraph text
     * @param string $style The paragraph style name
     * @return string The <text:p> element
     */
    private function paragraph(string $text, string $style): string {
        return '<text:p text:style-name="' . $style . '">' . $this->escape($text) . '</text:p>';
    }

    /**
     * The styles part declaring the Title and Standard paragraph styles.
     *
     * @return string The styles.xml part
     */
    private function styles(): string {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-styles '
            . 'xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" '
            . 'xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" '
            . 'xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" '
            . 'office:version="1.2">'
            . '<office:styles>'
            . '<style:style style:name="Standard" style:family="paragraph"/>'
            . '<style:style style:name="Title" style:family="paragraph" style:parent-style-name="Standard">'
            . '<style:text-properties fo:font-weight="bold" fo:font-size="18pt"/>'
            . '</style:style>'
            . '</office:styles>'
            . '</office:document-styles>';
    }

    /**
     * XML-escape a run of text.
     *
     * @param string $text The raw text
     * @return string The escaped text
     */
    private function escape(string $text): string {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
