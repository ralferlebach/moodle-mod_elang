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
 * Builds a minimal, valid DOCX (OOXML WordprocessingML) document from a title
 * and a list of paragraphs, with no third-party library.
 *
 * A DOCX is a ZIP of a handful of XML parts. This writer emits just the parts
 * Word (and LibreOffice) require to open the file: the content-type registry,
 * the package and document relationships, and a single document body. The
 * title becomes a Heading 1 paragraph, each transcript paragraph a normal
 * paragraph; the referenced Heading1 style is declared in styles.xml. Text is
 * XML-escaped, so any characters in a transcript are safe.
 *
 * Packing goes through Moodle's zip_packer (a core dependency), keeping the
 * writer free of any bundled library.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class docx_writer {
    /**
     * Build the DOCX bytes for a title and paragraphs.
     *
     * @param string $title The document title, rendered as Heading 1
     * @param string[] $paragraphs The body paragraphs, in order
     * @return string The DOCX file as a binary string
     */
    public function build(string $title, array $paragraphs): string {
        $files = [
            '[Content_Types].xml' => $this->content_types(),
            '_rels/.rels' => $this->package_rels(),
            'word/_rels/document.xml.rels' => $this->document_rels(),
            'word/document.xml' => $this->document($title, $paragraphs),
            'word/styles.xml' => $this->styles(),
        ];

        $packer = new \zip_packer();
        $tempfile = make_request_directory() . '/transcript.docx';
        $tostream = [];
        foreach ($files as $path => $content) {
            $tostream[$path] = [$content];
        }
        $packer->archive_to_pathname($tostream, $tempfile);
        $bytes = file_get_contents($tempfile);

        return $bytes === false ? '' : $bytes;
    }

    /**
     * The content-type registry naming every part in the package.
     *
     * @return string The [Content_Types].xml part
     */
    private function content_types(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-'
            . 'officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-'
            . 'officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    /**
     * The package-level relationships, pointing at the main document.
     *
     * @return string The _rels/.rels part
     */
    private function package_rels(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/'
            . 'officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
    }

    /**
     * The document-level relationships, pointing at the styles part.
     *
     * @return string The word/_rels/document.xml.rels part
     */
    private function document_rels(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/'
            . 'styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    /**
     * The document body: a Heading 1 title paragraph followed by one normal
     * paragraph per transcript paragraph.
     *
     * @param string $title The title text
     * @param string[] $paragraphs The body paragraphs
     * @return string The word/document.xml part
     */
    private function document(string $title, array $paragraphs): string {
        $body = $this->paragraph($title, 'Heading1');
        foreach ($paragraphs as $paragraph) {
            $body .= $this->paragraph($paragraph, null);
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . $body . '</w:body>'
            . '</w:document>';
    }

    /**
     * Render one paragraph, optionally in a named paragraph style.
     *
     * @param string $text The paragraph text
     * @param string|null $style A paragraph style id, or null for the default
     * @return string The <w:p> element
     */
    private function paragraph(string $text, ?string $style): string {
        $properties = $style === null ? '' : '<w:pPr><w:pStyle w:val="' . $style . '"/></w:pPr>';

        return '<w:p>' . $properties
            . '<w:r><w:t xml:space="preserve">' . $this->escape($text) . '</w:t></w:r>'
            . '</w:p>';
    }

    /**
     * The minimal styles part declaring the Heading1 style the title uses.
     *
     * @return string The word/styles.xml part
     */
    private function styles(): string {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:style w:type="paragraph" w:styleId="Heading1">'
            . '<w:name w:val="heading 1"/>'
            . '<w:rPr><w:b/><w:sz w:val="32"/></w:rPr>'
            . '</w:style>'
            . '</w:styles>';
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
