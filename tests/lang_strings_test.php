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
     * The language packs present, split into full packs and regional overrides.
     *
     * A regional pack such as es_mx or pt_br deliberately carries only the
     * strings that differ from its parent, so it cannot be held to the same
     * completeness rule. The split is by size rather than by a hand-kept list,
     * so a pack added later is covered without anyone remembering to register
     * it — which is how the first eighteen packs escaped this test.
     *
     * @return array [full, partial] — each a list of language directory names
     */
    private function packs(): array {
        global $CFG;

        $full = [];
        $partial = [];
        foreach (glob($CFG->dirroot . '/mod/elang/lang/*', GLOB_ONLYDIR) as $dir) {
            $lang = basename($dir);
            if (!is_readable($dir . '/elang.php')) {
                continue;
            }
            if (count($this->declared($lang)) < 400) {
                $partial[] = $lang;
            } else {
                $full[] = $lang;
            }
        }

        return [$full, $partial];
    }

    /**
     * The placeholders a string uses, as a sorted set.
     *
     * A translation that drops {$a->total} renders the literal text to a
     * learner; one that invents a placeholder English does not have renders
     * nothing at all. Both are invisible until someone opens that screen in
     * that language, which is why this is checked rather than reviewed.
     *
     * @param string $text
     * @return array
     */
    private function placeholders(string $text): array {
        preg_match_all('~\{\$a(->[a-z0-9_]+)?\}|%[a-z]+%~i', $text, $matches);
        $found = array_unique($matches[0]);
        sort($found);

        return $found;
    }

    /**
     * The string values of one language file, keyed by identifier.
     *
     * @param string $lang
     * @return array
     */
    private function values(string $lang): array {
        global $CFG;

        $source = file_get_contents($CFG->dirroot . '/mod/elang/lang/' . $lang . '/elang.php');
        preg_match_all('~^\$string\[\'([^\']+)\'\] = \'(.*?)\';$~ms', $source, $matches, PREG_SET_ORDER);

        $values = [];
        foreach ($matches as $match) {
            $values[$match[1]] = str_replace(["\\'", '\\\\'], ["'", '\\'], $match[2]);
        }

        return $values;
    }

    /**
     * Every full language pack declares exactly the identifiers English does.
     *
     * @return void
     */
    public function test_every_full_language_pack_matches_english(): void {
        $en = $this->declared('en');
        $this->assertNotEmpty($en);

        [$full] = $this->packs();
        $this->assertContains('de', $full, 'German should be a full pack.');
        $this->assertGreaterThan(1, count($full), 'Expected more than one full language pack.');

        foreach ($full as $lang) {
            $declared = $this->declared($lang);
            $this->assertSame(
                [],
                array_values(array_diff($en, $declared)),
                "Declared in English but missing from '$lang'."
            );
            $this->assertSame(
                [],
                array_values(array_diff($declared, $en)),
                "Declared in '$lang' but not in English."
            );
        }
    }

    /**
     * A regional override pack declares nothing English does not have.
     *
     * Completeness is not required of these — that is the point of them — but
     * an identifier that exists nowhere else is a typo that would silently
     * never be shown.
     *
     * @return void
     */
    public function test_regional_packs_only_override_known_strings(): void {
        $en = $this->declared('en');
        [, $partial] = $this->packs();

        foreach ($partial as $lang) {
            $declared = $this->declared($lang);
            $this->assertNotEmpty($declared, "Regional pack '$lang' declares nothing.");
            $this->assertSame(
                [],
                array_values(array_diff($declared, $en)),
                "Declared in '$lang' but not in English."
            );
        }
    }

    /**
     * Every translated string uses the same placeholders as its English source.
     *
     * @return void
     */
    public function test_placeholders_match_english(): void {
        $en = $this->values('en');
        $this->assertNotEmpty($en);

        [$full, $partial] = $this->packs();
        foreach (array_merge($full, $partial) as $lang) {
            if ($lang === 'en') {
                continue;
            }
            foreach ($this->values($lang) as $id => $text) {
                if (!isset($en[$id])) {
                    continue;
                }
                $this->assertSame(
                    $this->placeholders($en[$id]),
                    $this->placeholders($text),
                    "Placeholders differ from English in '$lang', string '$id'."
                );
            }
        }
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
