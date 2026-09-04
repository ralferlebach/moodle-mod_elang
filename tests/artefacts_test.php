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

namespace mod_elang;

/**
 * What the shipped JavaScript artefacts may and may not contain.
 *
 * A build artefact is committed, so nothing rebuilds it on the way into a
 * release: whatever is in the repository is what every installation gets. That
 * makes it the one kind of file that can quietly fall out of step with its
 * source and stay that way for months — which is what happened. A source map
 * from a development build was committed in August, never regenerated, and
 * shipped in every release afterwards still carrying the sources of two
 * components that had since been deleted.
 *
 * These assertions are cheap and they run in every PHPUnit job.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class artefacts_test extends \basic_testcase {
    /** Components that were removed and must not reappear in any artefact. */
    private const REMOVED_COMPONENTS = ['ImportPanel', 'MediaPanel'];

    /**
     * The plugin's own directory.
     *
     * @return string The absolute path
     */
    private function plugindir(): string {
        global $CFG;

        return $CFG->dirroot . '/mod/elang';
    }

    /**
     * The production bundle ships without a source map.
     *
     * build.mjs writes one only in development mode, so the bundle never
     * references a map and a map in the tree can only be a leftover. Shipping
     * it triples the size of the vendor directory and publishes the full
     * original source of everything in it.
     *
     * @return void
     */
    public function test_no_source_map_is_shipped(): void {
        $maps = glob($this->plugindir() . '/js/vendor/react/*.map');

        $this->assertSame(
            [],
            $maps,
            'A source map is present although the production build writes none. '
                . 'It is a leftover of a development build; delete it and add it to db/removed_files.txt.'
        );
    }

    /**
     * The bundle does not point at a map that is not there.
     *
     * A dangling sourceMappingURL makes every browser dev-tools session fetch
     * a 404, which is noise in exactly the place someone is trying to read.
     *
     * @return void
     */
    public function test_the_bundle_has_no_dangling_source_map_reference(): void {
        $bundle = file_get_contents($this->plugindir() . '/js/vendor/react/editor.bundle.js');

        $this->assertNotFalse($bundle);
        $this->assertStringNotContainsString('sourceMappingURL', $bundle);
    }

    /**
     * No deleted component survives in a shipped artefact.
     *
     * Their absence from the source tree is not enough: an artefact is
     * committed, so it keeps whatever was in it when it was last built.
     *
     * @return void
     */
    public function test_no_removed_component_survives_in_an_artefact(): void {
        $artefacts = array_merge(
            glob($this->plugindir() . '/js/vendor/react/*.js') ?: [],
            glob($this->plugindir() . '/js/vendor/react/*.map') ?: [],
            glob($this->plugindir() . '/amd/build/*') ?: []
        );
        $this->assertNotEmpty($artefacts);

        foreach ($artefacts as $path) {
            $contents = file_get_contents($path);
            $this->assertNotFalse($contents);

            foreach (self::REMOVED_COMPONENTS as $component) {
                $this->assertStringNotContainsString(
                    $component,
                    $contents,
                    basename($path) . " still contains $component, which no longer exists in the source. "
                        . 'Rebuild the artefact.'
                );
            }
        }
    }

    /**
     * Every path in db/removed_files.txt really is gone.
     *
     * The CI job checks this too, but it is the kind of list that is easy to
     * add to and easy to forget to act on, and a test says so in a second
     * rather than after a full pipeline.
     *
     * @return void
     */
    public function test_every_removed_file_is_actually_removed(): void {
        $listed = file($this->plugindir() . '/db/removed_files.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertNotFalse($listed);

        $paths = array_filter(
            array_map('trim', $listed),
            fn(string $line): bool => $line !== '' && !str_starts_with($line, '#')
        );
        $this->assertNotEmpty($paths, 'The list should still name at least the removed source map.');

        foreach ($paths as $path) {
            $this->assertFileDoesNotExist(
                $this->plugindir() . '/' . $path,
                "$path is listed as removed but is still present."
            );
        }
    }

    /**
     * README and db/access.php agree about who holds each capability.
     *
     * The archetype list in the README is what an administrator reads before
     * deciding whether to change anything, so a wrong entry there is worse than
     * no entry: it is read as authoritative. Three of them had drifted —
     * useregex was documented for editing teachers when only managers hold it,
     * exporttranscript omitted students, and deleteattempts named non-editing
     * teachers who do not have it.
     *
     * Rather than compare prose, this checks the two facts that matter and can
     * be checked: every capability is documented at all, and none is documented
     * that does not exist.
     *
     * @return void
     */
    public function test_the_readme_documents_exactly_the_declared_capabilities(): void {
        $access = file_get_contents($this->plugindir() . '/db/access.php');
        $readme = file_get_contents($this->plugindir() . '/README.md');
        $this->assertNotFalse($access);
        $this->assertNotFalse($readme);

        preg_match_all("~'(mod/elang:[a-z]+)'\s*=>\s*\[~", $access, $declared);
        $declared = array_unique($declared[1]);
        $this->assertNotEmpty($declared);

        preg_match_all('~\*\*(mod/elang:[a-z]+)\*\*~', $readme, $documented);
        $documented = array_unique($documented[1]);

        sort($declared);
        sort($documented);
        $this->assertSame(
            $declared,
            $documented,
            'README.md and db/access.php name different capabilities.'
        );
    }

    /**
     * The mobile service carries the learner functions and no authoring one.
     *
     * The file's own docblock used to claim every function was on the mobile
     * service. It is not, and it should not be: the authoring editor is a React
     * application for a desktop browser, so publishing a version from a context
     * that cannot show the editor would be an endpoint with no interface behind
     * it.
     *
     * @return void
     */
    public function test_only_learner_functions_are_on_the_mobile_service(): void {
        global $CFG;

        $functions = [];
        require($CFG->dirroot . '/mod/elang/db/services.php');

        $authoring = [
            'mod_elang_save_draft_version',
            'mod_elang_publish_version',
            'mod_elang_get_version_content',
            'mod_elang_preview_import',
            'mod_elang_generate_rule_gaps',
            'mod_elang_set_draft_media',
        ];

        foreach ($authoring as $name) {
            $this->assertArrayHasKey($name, $functions);
            $this->assertArrayNotHasKey(
                'services',
                $functions[$name],
                "$name is an authoring function and must not be on the mobile service."
            );
        }

        foreach (['mod_elang_start_attempt', 'mod_elang_submit_response', 'mod_elang_finish_attempt'] as $name) {
            $this->assertArrayHasKey($name, $functions);
            $this->assertContains(
                MOODLE_OFFICIAL_MOBILE_SERVICE,
                $functions[$name]['services'] ?? [],
                "$name is learner-facing and belongs on the mobile service."
            );
        }
    }
}
