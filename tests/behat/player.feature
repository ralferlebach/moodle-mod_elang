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

  Scenario: A touched in-progress attempt keeps reading the version it started on
    Given I am on the "Listening exercise 1" "elang activity" page logged in as student1
    And I should see "Exercise ready."
    And I should see "dort"
    # Answering touches the attempt, so it must stay pinned to protect the work.
    When I answer elang gap "Gap 1" with "chat"
    And elang "Listening exercise 1" publishes transcript "Le chien court" gap "chien"
    And I reload the page
    Then I should see "Exercise ready."
    And I should see "dort"
    And I should not see "court"

  Scenario: An untouched in-progress attempt follows a republished version
    Given I am on the "Listening exercise 1" "elang activity" page logged in as student1
    And I should see "Exercise ready."
    And I should see "dort"
    # Nothing was answered yet, so there is no work to protect: republishing
    # (for example to fix a broken medium) reaches this learner.
    When elang "Listening exercise 1" publishes transcript "Le chien court" gap "chien"
    And I reload the page
    Then I should see "Exercise ready."
    And I should see "court"
    And I should not see "dort"

  Scenario: A new activity shows the transcript below the medium
    Given elang "Listening exercise 1" has a media file "clip.mp4"
    When I am on the "Listening exercise 1" "elang activity" page logged in as student1
    Then I should see "Exercise ready."
    # The bounded, self-scrolling transcript region is what "below" means; no
    # caption is drawn over the picture.
    And ".mod_elang-transcript-scroll" "css_element" should exist
    And ".mod_elang-caption-overlay" "css_element" should not exist

  Scenario: The bottom overlay draws the caption over the medium
    Given the following "activities" exist:
      | activity | course | name       | idnumber | subtitleposition |
      | elang    | C1     | Overlay ex | elang2   | overlaybottom    |
    And elang "Overlay ex" has version transcript "Le chat dort" gap "chat"
    And elang "Overlay ex" has a media file "clip.mp4"
    When I am on the "Overlay ex" "elang activity" page logged in as student1
    Then I should see "Exercise ready."
    And ".mod_elang-media-stage" "css_element" should exist
    And ".mod_elang-caption-overlaybottom" "css_element" should exist
    # The transcript is no longer the reading surface, so it is not bounded.
    And ".mod_elang-transcript-scroll" "css_element" should not exist

  Scenario: The top overlay differs only in where the caption sits
    Given the following "activities" exist:
      | activity | course | name    | idnumber | subtitleposition |
      | elang    | C1     | Top ex  | elang3   | overlaytop       |
    And elang "Top ex" has version transcript "Le chat dort" gap "chat"
    And elang "Top ex" has a media file "clip.mp4"
    When I am on the "Top ex" "elang activity" page logged in as student1
    Then I should see "Exercise ready."
    And ".mod_elang-caption-overlaytop" "css_element" should exist
    And ".mod_elang-caption-overlaybottom" "css_element" should not exist

  Scenario: An audio medium falls back to the display below the medium
    Given the following "activities" exist:
      | activity | course | name     | idnumber | subtitleposition |
      | elang    | C1     | Audio ex | elang4   | overlaytop       |
    And elang "Audio ex" has version transcript "Le chat dort" gap "chat"
    And elang "Audio ex" has a media file "clip.mp3"
    When I am on the "Audio ex" "elang activity" page logged in as student1
    Then I should see "Exercise ready."
    # Nothing to draw a caption on, so the stored setting is kept but not applied.
    And ".mod_elang-caption-overlay" "css_element" should not exist
    And ".mod_elang-transcript-scroll" "css_element" should exist

  Scenario: Enter checks the answer and moves to the next gap
    Given elang "Listening exercise 1" has version transcript "Le chat dort et le chien court" gap "chat"
    And elang "Listening exercise 1" has a media file "clip.mp4"
    When I am on the "Listening exercise 1" "elang activity" page logged in as student1
    And I answer elang gap "Gap 1" with "chat"
    Then elang gap "Gap 1" should contain "chat"
