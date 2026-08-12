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

  Scenario: A fresh exercise shows the onboarding guidance
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I press "Edit content"
    Then I should see "Exercise content editor"
    And I should see "Start your exercise"

  Scenario: The editor offers the authoring toolbar
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I press "Edit content"
    Then I should see "Save draft"
    And I should see "Publish"
    And I should see "Add cue"
    And I should not see "The editor could not be loaded"
