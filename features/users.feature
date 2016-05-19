@users
Feature: Users
  As an API consumer
  I need to be able to manage the users

  Scenario: Retrieve a single user
    Given a user:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |
    Given a user:
      | username  | superman |
      | firstname | Clark    |
      | lastname  | Kent     |
    When I send GET request to "/users/batman"
    Then response status code should be 200
    And response entity should contain the values:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |