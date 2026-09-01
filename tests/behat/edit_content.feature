@mod @mod_elang
Feature: Reach the activity's working areas from its navigation
  In order to build and review listening exercises
  As a teacher
  I need media, subtitles, reports and the export to be modes of the activity

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | teacher2 | Teacher   | Two      | teacher2@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher2 | C1     | teacher        |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity | course | name       | idnumber |
      | elang    | C1     | Test elang | elang1   |

  Scenario: An editing teacher gets every working area as a mode
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    Then I should see "Media"
    And I should see "Subtitles & gaps"
    And I should see "Attempts"
    And I should see "Export" in the ".secondary-navigation" "css_element"

  Scenario: The old action buttons are gone from the exercise page
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    Then "Edit content" "button" should not exist
    And "Reports" "button" should not exist
    And "Export transcript" "button" should not exist

  Scenario: A learner is not offered the authoring or reporting modes
    Given I am on the "Test elang" "elang activity" page logged in as student1
    Then I should not see "Subtitles & gaps"
    And I should not see "Media"
    And I should not see "Attempts"
    # Asserted as a missing link rather than as missing text inside the
    # secondary navigation: with no mode of their own to reach, a learner gets
    # no secondary navigation region at all, and a locator for it would fail
    # for the wrong reason.
    And "Export" "link" should not exist

  Scenario: Allowing the worksheet download offers the export to a learner
    Given the following "activities" exist:
      | activity | course | name       | idnumber | allowtranscriptdownload |
      | elang    | C1     | Open elang | elang2   | 1                       |
    When I am on the "Open elang" "elang activity" page logged in as student1
    Then I should see "Export" in the ".secondary-navigation" "css_element"
    And I should not see "Subtitles & gaps"

  Scenario: Reports follow their own capability
    Given I am on the "Test elang" "elang activity" page logged in as teacher2
    Then I should see "Attempts"
    And I should not see "Subtitles & gaps"

  Scenario: The subtitle editor refuses to open before a medium exists
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Subtitles & gaps" from secondary navigation
    Then I should see "Add the video or audio file on the Media tab first"
    And I should not see "Exercise content editor"
    And "Go to Media" "button" should exist

  Scenario: The media page is reachable as its own mode
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Media" from secondary navigation
    Then I should see "Media"
    And I should see "Current medium"
    And I should see "No medium has been set for this exercise yet."
    And I should see "Other source"

  Scenario: A source address is stored as the medium and shown back
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Media" from secondary navigation
    When I expand all fieldsets
    And I set the field "Source address" to "https://example.org/lesson/clip.mp4"
    And I press "Save changes"
    Then I should see "https://example.org/lesson/clip.mp4"
    And I should not see "No medium has been set for this exercise yet."

  Scenario: A YouTube link is recognised as a provider rather than a plain URL
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Media" from secondary navigation
    When I expand all fieldsets
    And I set the field "Source address" to "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
    And I press "Save changes"
    Then I should see "YouTube"
    And I should see "dQw4w9WgXcQ"

  Scenario: An address that is neither a media URL nor a provider is refused
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Media" from secondary navigation
    When I expand all fieldsets
    And I set the field "Source address" to "not-an-address"
    And I press "Save changes"
    Then I should see "Enter a full address starting with"
    And I should see "No medium has been set for this exercise yet."

  Scenario: Setting a medium lets the subtitle editor open
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    And I select "Media" from secondary navigation
    And I expand all fieldsets
    And I set the field "Source address" to "https://example.org/lesson/clip.mp4"
    And I press "Save changes"
    When I select "Subtitles & gaps" from secondary navigation
    Then I should not see "Add the video or audio file on the Media tab first"

  Scenario: A teacher reaches the attempt report from the navigation
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Attempts" from secondary navigation
    Then I should see "Attempt reports"

  Scenario: A teacher reaches the transcript export from the navigation
    Given I am on the "Test elang" "elang activity" page logged in as teacher1
    When I select "Export" from secondary navigation
    Then I should see "Export transcript"
    And I should see "There is no published transcript to export yet."

  Scenario: The settings form offers the answer-grading options
    Given I am on the "Test elang" "elang activity editing" page logged in as teacher1
    Then I should see "Answer grading"
    And I should see "Playback and subtitles"
    And I should see "Transcript for learners"

  Scenario: A new activity starts with the subtitles below the medium
    Given I am on the "Test elang" "elang activity editing" page logged in as teacher1
    Then the field "Subtitle display" matches value "Below the medium"
    And the field "Playback at subtitle boundaries" matches value "Automatic"

  Scenario: The playback settings are stored per activity
    Given I am on the "Test elang" "elang activity editing" page logged in as teacher1
    When I set the following fields to these values:
      | Subtitle display                | On the medium — top |
      | Playback at subtitle boundaries | Always stop         |
    And I press "Save and display"
    And I am on the "Test elang" "elang activity editing" page
    Then the field "Subtitle display" matches value "On the medium — top"
    And the field "Playback at subtitle boundaries" matches value "Always stop"
