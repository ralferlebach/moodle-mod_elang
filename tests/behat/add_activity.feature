@mod @mod_elang
Feature: Add a language exercise activity
  In order to build listening exercises
  As a teacher
  I need to be able to add the language exercise activity to a course

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

  Scenario: A teacher adds a language exercise to a course
    Given I am on the "Course 1" course page logged in as teacher1
    And I turn editing mode on
    When I add a "elang" activity to course "Course 1" section "1" and I fill the form with:
      | Name        | Listening exercise 1        |
      | Description | A first listening exercise. |
    Then I should see "Listening exercise 1"

  Scenario: The activity page renders inside the Moodle page frame
    Given the following "activities" exist:
      | activity | course | name                 | idnumber |
      | elang    | C1     | Listening exercise 1 | elang1   |
    When I am on the "Listening exercise 1" "elang activity" page logged in as teacher1
    Then I should see "Listening exercise 1"
