@block @block_opencast
Feature: Add Opencast block as Teacher
  Overview and Add video / Edit upload tasks Page exists

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | T1       |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | config          | value                    | plugin         |
      | apiurl          | 172.17.0.1:8080          | tool_opencast  |
      | apipassword     | opencast                 | tool_opencast  |
      | apiusername     | admin                    | tool_opencast  |
      | limituploadjobs | 0                        | block_opencast |
      | group_creation  | 0                        | block_opencast |
      | group_name      | Moodle_course_[COURSEID] | block_opencast |
      | series_name     | Course_Series_[COURSEID] | block_opencast |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Opencast Videos" block

  Scenario: The Opencast Videos block is Added
    Then I should see "No videos available"

  Scenario: Opencast Overview page implemented
    When I click on "Go to overview..." "link"
    Then I should not see "Videos scheduled to be transferred to Opencast"
    And I should see "Videos available in this course"
    And I should see "No videos available"

  Scenario: Opencast Add video page implemented
    Given I click on "Go to overview..." "link"
    When I click on "Add video" "button"
    Then I should see "You can drag and drop files here to add them."

  @_file_upload @javascript
  Scenario: Opencast Upload Video
    Given I click on "Go to overview..." "link"
    And I click on "Add video" "button"
    And I set the field "Title" to "Test"
    And I upload "blocks/opencast/tests/fixtures/test.mp4" file to "Presenter video" filemanager
    And I click on "Save changes" "button"
    Then I should see "Test"
    And I should see "test.mp4"
    And I should see "Ready for transfer"

  @_file_upload @javascript
  Scenario: Opencast run cronjob
    Given I click on "Go to overview..." "link"
    And I click on "Add video" "button"
    And I set the field "Title" to "Test"
    And I upload "blocks/opencast/tests/fixtures/test.mp4" file to "Presenter video" filemanager
    And I click on "Save changes" "button"
    And I run the scheduled task "\block_opencast\task\process_upload_cron"
    And I wait "10" seconds
    And I run the scheduled task "\block_opencast\task\process_upload_cron"
    And I wait "10" seconds
    And I reload the page
    Then I should not see "Ready for transfer"
    And I should see "Test"

  @_file_upload @javascript
  Scenario: Opencast remove Series ID
    Given I click on "Go to overview..." "link"
    And I click on "Add video" "button"
    And I set the field "Title" to "Test"
    And I upload "blocks/opencast/tests/fixtures/test.mp4" file to "Presenter video" filemanager
    And I click on "Save changes" "button"
    And I run the scheduled task "\block_opencast\task\process_upload_cron"
    And I wait "10" seconds
    And I run the scheduled task "\block_opencast\task\process_upload_cron"
    And I wait "10" seconds
    And I click on "Edit series mapping" "link"
    And I set the field "seriesid" to ""
    And I click on "Save changes" "button"
    Then I should see "The series ID was removed."
    And I should see "Create new series"
    And I should see "No videos available"
    And I should not see "Test"
