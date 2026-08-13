@mod @mod_elang @javascript
Feature: Attempt a language exercise in the player
  In order to practise filling gaps in a transcript
  As a learner
  I need the player to keep my work and stay on the version I started

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "activities" exist:
      | activity | course | name                 | idnumber |
      | elang    | C1     | Listening exercise 1 | elang1   |
    And elang "Listening exercise 1" has version transcript "Le chat dort" gap "chat"

  Scenario: The player renders the transcript with a gap
    When I am on the "Listening exercise 1" "elang activity" page logged in as student1
    Then I should see "Exercise ready."
    And I should see "dort"

  Scenario: The player loads the exercise medium
    Given elang "Listening exercise 1" has a media file "clip.mp4"
    When I am on the "Listening exercise 1" "elang activity" page logged in as student1
    Then I should see "Exercise ready."
    # The media element is present and its source resolves through pluginfile.php
    # (a real <video> with a source, not an empty frame).
    And "video source" "css_element" should exist
    And I should see "Exercise ready."

  Scenario: A learner's answer survives a page reload
    Given I am on the "Listening exercise 1" "elang activity" page logged in as student1
    And I should see "Exercise ready."
    When I answer elang gap "Gap 1" with "chat"
    Then I should see "Correct"
    When I reload the page
    Then I should see "Exercise ready."
    And elang gap "Gap 1" should contain "chat"
    And I should see "Correct"

  Scenario: An in-progress attempt keeps reading the version it started on
    Given I am on the "Listening exercise 1" "elang activity" page logged in as student1
    And I should see "Exercise ready."
    And I should see "dort"
    When elang "Listening exercise 1" publishes transcript "Le chien court" gap "chien"
    And I reload the page
    Then I should see "Exercise ready."
    And I should see "dort"
    And I should not see "court"
