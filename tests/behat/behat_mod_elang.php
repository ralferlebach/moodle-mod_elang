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

use Behat\Mink\Exception\ExpectationException;

/**
 * Behat step definitions for mod_elang.
 *
 * Provides the steps the player scenarios need that Moodle core does not: since
 * there is no authoring UI yet, published exercise versions are created here
 * directly through the domain layer, and a gap is answered by driving the
 * player's own submit path.
 *
 * @package    mod_elang
 * @category   test
 * @copyright  2026 Ralf Erlebach
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_elang extends behat_base {
    /**
     * Publish a version with one cue and one gap for a named activity.
     *
     * @Given /^elang "(?P<name>[^"]*)" has version transcript "(?P<transcript>[^"]*)" gap "(?P<solution>[^"]*)"$/
     *
     * @param string $name The activity name
     * @param string $transcript The cue transcript, containing the solution text
     * @param string $solution The word that becomes the gap
     * @return void
     */
    public function elang_has_version(string $name, string $transcript, string $solution): void {
        $this->publish_elang_version($name, $transcript, $solution);
    }

    /**
     * Publish a further version for a named activity, mid-scenario.
     *
     * @When /^elang "(?P<name>[^"]*)" publishes transcript "(?P<transcript>[^"]*)" gap "(?P<solution>[^"]*)"$/
     *
     * @param string $name The activity name
     * @param string $transcript The cue transcript, containing the solution text
     * @param string $solution The word that becomes the gap
     * @return void
     */
    public function elang_publishes_version(string $name, string $transcript, string $solution): void {
        $this->publish_elang_version($name, $transcript, $solution);
    }

    /**
     * Answer a gap in the player by its accessible label and submit it the way
     * pressing Enter in the field does.
     *
     * @When /^I answer elang gap "(?P<label>[^"]*)" with "(?P<text>[^"]*)"$/
     *
     * @param string $label The gap input's accessible label, e.g. "Gap 1"
     * @param string $text The answer to type
     * @return void
     */
    public function i_answer_elang_gap(string $label, string $text): void {
        $label = addslashes($label);
        $text = addslashes($text);
        $js = <<<JS
(function() {
    var input = document.querySelector('input[aria-label="{$label}"]');
    if (!input) {
        throw new Error('elang gap not found: {$label}');
    }
    input.value = '{$text}';
    input.dispatchEvent(new Event('input', {bubbles: true}));
    input.dispatchEvent(new KeyboardEvent('keydown', {key: 'Enter', bubbles: true}));
})();
JS;
        $this->execute_script($js);
    }

    /**
     * Assert a gap input holds a given value, located by its accessible label.
     *
     * The gaps carry an aria-label rather than an associated <label>, which
     * Moodle's built-in "the field ... matches value" locator does not match,
     * so this reads the value directly by aria-label.
     *
     * @Then /^elang gap "(?P<label>[^"]*)" should contain "(?P<text>[^"]*)"$/
     *
     * @param string $label The gap input's accessible label, e.g. "Gap 1"
     * @param string $text The value the input is expected to hold
     * @return void
     */
    public function elang_gap_should_contain(string $label, string $text): void {
        $node = $this->find('css', 'input[aria-label="' . $label . '"]');
        $actual = (string) $node->getValue();
        if ($actual !== $text) {
            throw new ExpectationException(
                "The elang gap \"{$label}\" contains \"{$actual}\", expected \"{$text}\".",
                $this->getSession()
            );
        }
    }

    /**
     * Create a draft version with one cue and one gap for the named activity,
     * then publish it.
     *
     * @param string $name The activity name (elang.name)
     * @param string $transcript The cue transcript, which must contain $solution
     * @param string $solution The word within the transcript that becomes the gap
     * @return void
     */
    protected function publish_elang_version(string $name, string $transcript, string $solution): void {
        global $DB;

        $elang = $DB->get_record('elang', ['name' => $name], '*', MUST_EXIST);

        $charstart = mb_strpos($transcript, $solution, 0, 'UTF-8');
        if ($charstart === false) {
            throw new \coding_exception("Gap solution '{$solution}' is not present in transcript '{$transcript}'");
        }

        $manager = new \mod_elang\local\domain\version_manager();
        $draft = $manager->create_draft((int) $elang->id);

        $cue = (object) [
            'versionid' => $draft->id,
            'cuekey' => 'behat-cue-' . random_string(8),
            'sortorder' => 1,
            'starttime' => 0,
            'endtime' => 5000,
            'transcript' => $transcript,
            'transcriptformat' => FORMAT_PLAIN,
        ];
        $cue->id = $DB->insert_record('elang_cue', $cue);

        $gap = (object) [
            'cueid' => $cue->id,
            'gapkey' => 'behat-gap-' . random_string(8),
            'sortorder' => 1,
            'charstart' => $charstart,
            'charlength' => mb_strlen($solution, 'UTF-8'),
            'solution' => $solution,
            'gradingalgorithm' => \mod_elang\local\grading\answer_evaluator::ALGORITHM_EXACT,
        ];
        $gap->id = $DB->insert_record('elang_gap', $gap);

        $manager->publish((int) $draft->id);
    }

    /**
     * Record a finished attempt for a named user against a named activity's
     * published version: start the attempt, answer its single gap and finish it.
     * Lets a report scenario show real attempt data without driving the
     * JavaScript player.
     *
     * @Given /^elang "(?P<name>[^"]*)" has a finished attempt by "(?P<username>[^"]*)" answering "(?P<text>[^"]*)"$/
     *
     * @param string $name The activity name (elang.name)
     * @param string $username The username making the attempt
     * @param string $text The text to submit for the version's single gap
     * @return void
     */
    public function elang_has_a_finished_attempt(string $name, string $username, string $text): void {
        global $DB;

        $elang = $DB->get_record('elang', ['name' => $name], '*', MUST_EXIST);
        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);

        $manager = new \mod_elang\local\domain\version_manager();
        $version = $manager->get_published((int) $elang->id);
        if ($version === null) {
            throw new \coding_exception("elang '{$name}' has no published version to attempt.");
        }

        $cue = $DB->get_record('elang_cue', ['versionid' => $version->id], '*', MUST_EXIST);
        $gap = $DB->get_record('elang_gap', ['cueid' => $cue->id], '*', MUST_EXIST);

        $evaluator = new \mod_elang\local\grading\answer_evaluator(
            new \mod_elang\local\grading\script_handler_manager([])
        );
        $attempts = new \mod_elang\local\domain\attempt_manager($evaluator);

        $attempt = $attempts->start_attempt((int) $elang->id, (int) $user->id, (int) $version->id);
        $attempts->submit_response((int) $attempt->id, (int) $gap->id, $text);
        $attempts->finish_attempt((int) $attempt->id);
    }
}
