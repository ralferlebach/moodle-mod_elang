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
     * Language packs that deliberately carry only what differs from a parent.
     *
     * Named rather than guessed. An earlier version of this test split the
     * packs by counting strings — under four hundred meant regional — which
     * worked only by accident of the current contents. An incomplete full pack
     * with four hundred and one strings would have been waved through as
     * complete, and that is precisely the case the test exists to catch.
     *
     * Anything not named here is required to be complete, so forgetting to
     * register a new regional pack makes the suite fail loudly rather than
     * quietly lower the bar.
     */
    private const REGIONAL_PACKS = ['es_mx', 'pt_br'];

    /**
     * The language packs present, split into full packs and regional overrides.
     *
     * @return array [full, regional] — each a list of language directory names
     */
    private function packs(): array {
        global $CFG;

        $full = [];
        $regional = [];
        foreach (glob($CFG->dirroot . '/mod/elang/lang/*', GLOB_ONLYDIR) as $dir) {
            $lang = basename($dir);
            if (!is_readable($dir . '/elang.php')) {
                continue;
            }
            if (in_array($lang, self::REGIONAL_PACKS, true)) {
                $regional[] = $lang;
            } else {
                $full[] = $lang;
            }
        }

        return [$full, $regional];
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
        preg_match_all('~\\{\\$a(->[a-z0-9_]+)?\\}|%[a-z]+%~i', $text, $matches);
        $found = array_unique($matches[0]);
        sort($found);

        return $found;
    }

    /**
     * The strings of one language file, as PHP itself sees them.
     *
     * Executed in an isolated scope rather than scraped with a regular
     * expression. A pattern only matches the shape it was written for, so a
     * string defined in any other valid way would have been silently skipped by
     * the placeholder check — present according to one method, absent according
     * to the other, and reported by neither.
     *
     * @param string $lang
     * @return array Identifier => translated text
     */
    private function values(string $lang): array {
        global $CFG;

        $load = static function (string $path): array {
            $string = [];
            require($path);

            return $string;
        };

        return $load($CFG->dirroot . '/mod/elang/lang/' . $lang . '/elang.php');
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
     * No language file declares the same identifier twice.
     *
     * PHP keeps the last assignment and says nothing, so a duplicate is a
     * translation that exists in the file, is read by every reviewer, and is
     * never shown to anyone.
     *
     * @return void
     */
    public function test_no_language_file_declares_a_string_twice(): void {
        [$full, $regional] = $this->packs();

        foreach (array_merge($full, $regional) as $lang) {
            $declared = $this->declared($lang);
            $counts = array_count_values($declared);
            $duplicates = array_keys(array_filter($counts, static fn($n) => $n > 1));

            $this->assertSame(
                [],
                $duplicates,
                "Declared more than once in '$lang': " . implode(', ', $duplicates)
            );
        }
    }

    /**
     * What PHP loads from a language file is what the file appears to declare.
     *
     * The two readings are independent: one executes the file, the other scans
     * its text. If they disagree, a string is being defined in a way one of
     * them cannot see — and the checks built on the weaker reading would pass
     * by skipping it rather than by finding it correct.
     *
     * @return void
     */
    public function test_every_declared_string_is_actually_loaded(): void {
        [$full, $regional] = $this->packs();

        foreach (array_merge($full, $regional) as $lang) {
            $declared = $this->declared($lang);
            $loaded = array_keys($this->values($lang));
            sort($declared);
            sort($loaded);

            $this->assertSame(
                $declared,
                $loaded,
                "The strings PHP loads from '$lang' differ from the ones the file appears to declare."
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
     * Fixture data and core labels the browser tests legitimately assert on.
     *
     * A browser test types names and answers into the site and then checks they
     * came back, and it presses buttons Moodle itself provides. Neither comes
     * from this plugin's language file. They are listed rather than pattern-
     * matched away, so the list stays short and visible instead of quietly
     * growing into a hole in the check.
     */
    private const NOT_PLUGIN_STRINGS = [
        // Fixture data created by the tests themselves. The last three were
        // hidden until the wildcard bug above was fixed: a run of placeholders
        // matched them, so they looked like plugin text and were never listed.
        'Listening exercise 1',
        'dort',
        'court',
        'Student One',
        'dQw4w9WgXcQ',
        // Moodle core.
        'Save changes',
        'Course 1',
        'Save and display',
        'Save and return to course',
        'Add an activity or resource',
        'Continue',
        'Log in',
        'Dashboard',
    ];

    /**
     * Every visible label the browser tests assert on still exists in English.
     *
     * The end-to-end tests match on what a person reads, which is the right
     * thing for them to do and makes them break whenever a label is reworded.
     * Renaming cue to subtitle across the interface broke three Behat scenarios
     * and one Playwright test; Behat surfaced after two minutes locally,
     * Playwright after four minutes in CI. This finds the same drift in
     * milliseconds, before either runs.
     *
     * A literal counts as present if any English string contains it, because
     * getByText and Behat's "I should see" both match substrings. Strings with
     * a %count%-style token are compared with the token treated as a wildcard,
     * since what reaches the page has a number in its place.
     *
     * @return void
     */
    public function test_browser_test_labels_exist_in_english(): void {
        global $CFG;

        $values = array_values($this->values('en'));
        $this->assertNotEmpty($values);

        // Compare word by word rather than as raw text. A label on the page has
        // had its placeholders filled in and may be quoted without its final
        // full stop, so neither string contains the other literally.
        $words = static function (string $text): array {
            $text = preg_replace('~\{\$a(->[a-z0-9_]+)?\}|%[a-z]+%~i', ' * ', $text);
            $text = preg_replace('~[^\p{L}\p{N}*]+~u', ' ', $text);

            return array_values(array_filter(explode(' ', trim($text)), 'strlen'));
        };

        $english = array_map(static fn($text) => $words((string)$text), $values);

        // True when the literal's words appear in order, as a run, inside the
        // English string — with * standing for whatever filled a placeholder.
        $contains = static function (array $haystack, array $needle): bool {
            if ($needle === [] || count($needle) > count($haystack)) {
                return false;
            }
            $limit = count($haystack) - count($needle);
            for ($offset = 0; $offset <= $limit; $offset++) {
                $literal = false;
                foreach ($needle as $index => $word) {
                    $against = $haystack[$offset + $index];
                    if ($against === '*') {
                        // A placeholder stands for whatever filled it, so it
                        // matches any single word.
                        continue;
                    }
                    if (strcasecmp($against, $word) !== 0) {
                        continue 2;
                    }
                    $literal = true;
                }

                // At least one real word has to have lined up. Without this a
                // run of placeholders matches anything of the same length:
                // "Cue {$a} ({$a})" reduces to [Cue, *, *], and the tail [*, *]
                // then "contained" the stale label "Add cue" — so the check
                // that exists to catch renamed labels waved one through.
                if ($literal) {
                    return true;
                }
            }

            return false;
        };

        $sources = array_merge(
            glob($CFG->dirroot . '/mod/elang/tests/playwright/tests/*.spec.ts') ?: [],
            glob($CFG->dirroot . '/mod/elang/tests/behat/*.feature') ?: []
        );
        $this->assertNotEmpty($sources, 'No browser tests found to check.');

        $patterns = [
            '~getBy(?:Text|Label)\(\s*\'([^\']+)\'~',
            '~getByRole\([^)]*name:\s*\'([^\']+)\'~',
            '~I (?:should see|press|click on|follow) "([^"]+)"~',
        ];

        $missing = [];
        foreach ($sources as $file) {
            $source = file_get_contents($file);
            foreach ($patterns as $pattern) {
                preg_match_all($pattern, $source, $matches);
                foreach ($matches[1] as $literal) {
                    if (in_array($literal, self::NOT_PLUGIN_STRINGS, true)) {
                        continue;
                    }
                    // A CSS selector is a locator and a URL is data a test
                    // types in; neither is a label anyone reads off the page.
                    if (preg_match('~^[.#\[]~', $literal) || strpos($literal, '://') !== false) {
                        continue;
                    }
                    $needle = $words($literal);
                    foreach ($english as $haystack) {
                        if ($contains($haystack, $needle)) {
                            continue 2;
                        }
                    }
                    $entry = basename($file) . ': "' . $literal . '"';
                    if (!in_array($entry, $missing, true)) {
                        $missing[] = $entry;
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $missing,
            "Browser tests assert on text no English string contains:\n" . implode("\n", $missing)
        );
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
