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
    When I select "Attempts" from secondary navigation
    Then I should see "Attempts"
    And I should see "No attempts yet."

  Scenario: A finished attempt appears in the report
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Attempts" from secondary navigation
    Then I should see "Attempts"
    And I should see "Student One"
    And I should not see "No attempts yet."
    And I should see "Attempts shown"
    And I should see "Finished" in the ".mod_elang-report" "css_element"

  Scenario: A learner is offered no teaching mode on the activity
    Given I am on the "Test elang" "elang activity" page logged in as student1
    Then I should not see "Subtitles & gaps"
    And I should not see "Media"
    And I should not see "Attempts"

  Scenario: An attempt detail groups the gaps under their cue
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Attempts" from secondary navigation
    When I follow "Student One"
    Then I should see "Answered"
    And I should see "Needed a hint"
    And ".mod_elang-attemptcue" "css_element" should exist
    # The result is a glyph carrying its wording as an accessible name, not a
    # word in the table cell.
    And ".mod_elang-attemptdetail .fa" "css_element" should exist
    And I should see "Back to all attempts"

  Scenario: The figures describe the attempts in view
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Attempts" from secondary navigation
    Then I should see "Attempts shown"
    And I should see "Average score (finished)"
    And I should see "Used a hint"

  Scenario: Filtering by a state with no attempts says so
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Attempts" from secondary navigation
    When I set the field "State" to "In progress"
    And I press "Apply filters"
    Then I should see "No attempt matches these filters."
    And I should see "Clear filters"

  Scenario: Clearing the filters brings the attempts back
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Attempts" from secondary navigation
    And I set the field "State" to "In progress"
    And I press "Apply filters"
    When I follow "Clear filters"
    Then I should see "Student One"
    And I should not see "Clear filters"

  Scenario: The export formats sit behind one menu
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Attempts" from secondary navigation
    Then "Export" "button" should exist
    # All four stay available, but behind one control rather than as four
    # equal links in the page.
    And "XLSX" "link" should exist in the ".mod_elang-exportformats" "css_element"
    And "CSV" "link" should exist in the ".mod_elang-exportformats" "css_element"
    And "ODS" "link" should exist in the ".mod_elang-exportformats" "css_element"
    And "JSON" "link" should exist in the ".mod_elang-exportformats" "css_element"

  Scenario: Deleting is not offered beside viewing
    Given elang "Test elang" has a finished attempt by "student1" answering "chat"
    And I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Attempts" from secondary navigation
    # The destructive action lives in the row's action menu, not next to the
    # primary one, so it cannot be hit by aiming at "View".
    Then "Delete" "link" should exist in the ".action-menu" "css_element"
