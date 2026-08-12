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

namespace mod_elang\external;

use mod_elang\fixtures\authoring_test_fixture_builder;

/**
 * Tests for the generate_rule_gaps web service.
 *
 * @package    mod_elang
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_elang\external\generate_rule_gaps
 */
final class generate_rule_gaps_test extends \advanced_testcase {
    /** @var \stdClass The editing teacher. */
    private $teacher;

    /** @var \stdClass The draft version being edited. */
    private $draft;

    /**
     * Build a course, activity, draft and teacher for each test.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        require_once(__DIR__ . '/../fixtures/authoring_test_fixture.php');
        $fixture = authoring_test_fixture_builder::create($this);
        $this->teacher = $fixture->teacher;
        $this->draft = $fixture->draft;
    }

    /**
     * A word-list rule returns the matching gaps as codepoint spans.
     *
     * @return void
     */
    public function test_word_list_rule_returns_gaps(): void {
        $this->setUser($this->teacher);

        $result = generate_rule_gaps::execute((int) $this->draft->id, 'Le chat dort, le chat court', [
            'type' => 'words',
            'words' => ['chat'],
        ]);

        $this->assertCount(2, $result['gaps']);
        $this->assertSame(3, $result['gaps'][0]['charstart']);
        $this->assertSame(4, $result['gaps'][0]['charlength']);
        $this->assertSame('chat', $result['gaps'][0]['solution']);
    }

    /**
     * An every-nth rule returns every nth word.
     *
     * @return void
     */
    public function test_every_nth_rule_returns_gaps(): void {
        $this->setUser($this->teacher);

        $result = generate_rule_gaps::execute((int) $this->draft->id, 'one two three four', [
            'type' => 'everynth',
            'n' => 2,
        ]);

        $solutions = array_map(fn($gap) => $gap['solution'], $result['gaps']);
        $this->assertSame(['one', 'three'], $solutions);
    }

    /**
     * An unknown rule type is rejected with a clear message.
     *
     * @return void
     */
    public function test_unknown_rule_type_is_rejected(): void {
        $this->setUser($this->teacher);

        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessageMatches('/Unknown gap rule type/');
        generate_rule_gaps::execute((int) $this->draft->id, 'one two', ['type' => 'nonsense']);
    }

    /**
     * A user without the manage capability is refused.
     *
     * @return void
     */
    public function test_requires_manage_capability(): void {
        $this->setUser($this->getDataGenerator()->create_user());

        $this->expectException(\moodle_exception::class);
        generate_rule_gaps::execute((int) $this->draft->id, 'one two', ['type' => 'words', 'words' => ['one']]);
    }
}
