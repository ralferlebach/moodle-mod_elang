@mod @mod_elang @javascript
Feature: The Subtitle Studio authoring editor
  In order to build an exercise
  As a teacher
  I need the editor to load, guide me when it is empty and offer the authoring tools

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | name       | idnumber |
      | elang    | C1     | Test elang | elang1   |
    # The subtitle editor only opens once the draft has a medium to time
    # against, so every scenario below starts from a draft that has one.
    And elang "Test elang" has a draft medium

  Scenario: A fresh exercise shows the onboarding guidance
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Subtitles & gaps" from secondary navigation
    Then I should see "Exercise content editor"
    And I should see "Start your exercise"

  Scenario: The editor offers the authoring toolbar
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Subtitles & gaps" from secondary navigation
    Then I should see "Save draft"
    And I should see "Publish"
    And I should see "Add cue"
    And I should not see "The editor could not be loaded"

  Scenario: Adding a cue creates a cue row and autosaves it
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Subtitles & gaps" from secondary navigation
    When I press "Add cue"
    Then I should see "Transcript"
    And I should see "All changes saved"

  Scenario: The learner preview hides a gap solution
    Given elang "Test elang" has version transcript "Le chat dort" gap "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Subtitles & gaps" from secondary navigation
    When I press "Learner preview"
    Then I should not see "chat" in the "[data-region=maskedpreview]" "css_element"

  Scenario: A cue start edge can be nudged later with the keyboard
    Given elang "Test elang" has version transcript "Le chat dort" gap "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Subtitles & gaps" from secondary navigation
    And I should see "Transcript"
    # The seeded cue starts at 0 ms; one right-arrow press nudges the start edge
    # by 100 ms (the browser delivers this as KeyboardEvent.key 'ArrowRight').
    When I click on ".mod_elang-editor-timeline-handle.start" "css_element"
    And I press the right key
    Then the "aria-valuenow" attribute of ".mod_elang-editor-timeline-handle.start" "css_element" should contain "100"

  Scenario: Gaps can be generated from a word-list rule
    Given elang "Test elang" has version transcript "Le chat dort" gap "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Subtitles & gaps" from secondary navigation
    And I should see "Transcript"
    When I set the field "Words to blank out" to "dort"
    And I press "Generate gaps"
    And I press "Apply 1 gaps"
    Then I should see "Created 1 gaps from the rule"
