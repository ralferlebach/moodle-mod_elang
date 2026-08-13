@mod @mod_elang
Feature: Review learner attempts in the report
  In order to follow how learners do
  As a teacher
  I need the attempt report to show attempts and stay off-limits to learners

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name       | idnumber |
      | elang    | C1     | Test elang | elang1   |
    And elang "Test elang" has version transcript "Le chat dort" gap "chat"

  Scenario: The report shows an empty state before anyone attempts
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I press "Reports"
    Then I should see "Attempt reports"
    And I should see "No attempts yet."

  Scenario: A finished attempt appears in the report
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    When I press "Reports"
    Then I should see "Attempt reports"
    And I should see "Student One"
    And I should not see "No attempts yet."

  Scenario: A learner sees only the learner actions on the activity
    Given I am on the "Test elang" "elang activity" page logged in as student1
    Then I should see "Export transcript" in the ".mod_elang-actions" "css_element"
    And I should not see "Edit content" in the ".mod_elang-actions" "css_element"
    And I should not see "Reports" in the ".mod_elang-actions" "css_element"
