@mod @mod_elang
Feature: Reach the content authoring tools from the activity
  In order to build listening exercises
  As a teacher
  I need the editor, report and export to be reachable from the activity page

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

  Scenario: A teacher reaches the content editor from the activity page
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    Then I should see "Edit content"
    When I press "Edit content"
    Then I should see "Exercise content editor"

  Scenario: A teacher reaches the attempt report from the activity page
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    Then I should see "Reports"
    When I press "Reports"
    Then I should see "Attempt reports"

  Scenario: A teacher reaches the transcript export from the activity page
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    Then I should see "Export transcript"
    When I press "Export transcript"
    Then I should see "There is no published transcript to export yet."

  Scenario: The settings form offers the answer-grading options
    Given I am on the "Test elang" "elang activity editing" page logged in as teacher1
    Then I should see "Answer grading"
