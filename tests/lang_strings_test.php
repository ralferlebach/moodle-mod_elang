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
 * The language files, against the code that asks for them.
 *
 * These checks exist because of one near-miss. Renaming every string id from
 * "player:ready" to "player_ready" was done by search and replace, which
 * cannot see an id assembled at run time — and one such call reached CI as
 * "Invalid get_string() identifier: 'provider:youtube'", surfacing in a test
 * that was exercising something else entirely.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class lang_strings_test extends \basic_testcase {
    /**
     * The identifiers declared in one language file.
     *
     * @param string $lang The language directory, en or de
     * @return array The identifiers
     */
    private function declared(string $lang): array {
        global $CFG;

        $source = file_get_contents($CFG->dirroot . '/mod/elang/lang/' . $lang . '/elang.php');
        preg_match_all('~^\$string\[\'([^\']+)\'\]~m', $source, $matches);

        return $matches[1];
    }

    /**
     * Every source file of this plugin, excluding what is generated or vendored.
     *
     * @return array Absolute paths
     */
    private function sources(): array {
        global $CFG;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $CFG->dirroot . '/mod/elang',
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $files = [];
        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if (preg_match('~/(node_modules|amd/build|js/vendor|\.git)/~', $path)) {
                continue;
            }
            if (preg_match('~\.(php|js|ts|tsx|mustache)$~', $path)) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * German and English declare exactly the same identifiers.
     *
     * @return void
     */
    public function test_the_two_language_files_agree(): void {
        $en = $this->declared('en');
        $de = $this->declared('de');

        $this->assertNotEmpty($en);
        $this->assertSame([], array_values(array_diff($en, $de)), 'Declared in English but not in German.');
        $this->assertSame([], array_values(array_diff($de, $en)), 'Declared in German but not in English.');
    }

    /**
     * Only capability strings may contain a colon.
     *
     * Moodle and AMOS accept [a-z0-9_] in a string id. A colon anywhere else
     * cannot be published to the plugin directory or translated on
     * lang.moodle.org — except in the strings that name the capabilities
     * themselves, which have to match them exactly.
     *
     * @return void
     */
    public function test_only_capability_strings_contain_a_colon(): void {
        foreach ($this->declared('en') as $id) {
            if (strpos($id, ':') === false) {
                continue;
            }
            $this->assertStringStartsWith(
                'elang:',
                $id,
                "$id contains a colon but does not name a capability."
            );
        }
    }

    /**
     * Every identifier the code assembles at run time has strings behind it.
     *
     * A quoted prefix concatenated with a variable — get_string('provider_' .
     * $key) — is invisible to a search for a literal id, which is exactly how
     * one was left behind when the ids were flattened. This cannot know which
     * key will be appended, so it checks the weaker but still useful property:
     * that at least one declared string starts with that prefix.
     *
     * @return void
     */
    public function test_assembled_identifiers_have_strings_behind_them(): void {
        $declared = $this->declared('en');
        $unresolved = [];

        foreach ($this->sources() as $path) {
            $code = file_get_contents($path);

            // Only inside a string lookup: a concatenated prefix elsewhere is a
            // lock name or a CSS class, not a string id. The prefix may end in
            // an underscore or a colon — the colon form is the mistake this
            // test exists for, so it has to be matched in order to be rejected.
            preg_match_all('~get_string\(\s*\'([a-z][a-z0-9_]*[_:])\'\s*\.~', $code, $php);
            preg_match_all('~\bt\(\s*\'([a-z][a-z0-9_]*[_:])\'\s*\+~', $code, $js);

            foreach (array_unique(array_merge($php[1], $js[1])) as $prefix) {
                if (substr($prefix, -1) === ':') {
                    // No assembled id may use the colon form. The ten strings
                    // that still contain a colon name capabilities and are
                    // never built from a variable.
                    $unresolved[] = basename($path) . ": '{$prefix}' builds a colon identifier";
                    continue;
                }

                $found = false;
                foreach ($declared as $id) {
                    if (strpos($id, $prefix) === 0) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $unresolved[] = basename($path) . ": '{$prefix}' matches no declared string";
                }
            }
        }

        $this->assertSame([], $unresolved, implode("\n", $unresolved));
    }
}
