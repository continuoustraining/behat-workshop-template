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

  Scenario: Create a new user
    When I send POST request to "/users" with payload from "create-user.json"
    Then response status code should be 201
    And response entity should contain the values:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |
    And a "user" with the following data should have been created:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |

  Scenario: Retrieve the list of users
    Given a user:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |
    Given a user:
      | username  | superman |
      | firstname | Clark    |
      | lastname  | Kent     |
    When I send GET request to "/users"
    Then response status code should be 200
    And response should be a collection of "users"
    And response collection should contain exactly 2 "users"
    And response collection "users" should contain the resource:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |
    And response collection "users" should contain the resource:
      | username  | superman |
      | firstname | Clark    |
      | lastname  | Kent     |

  Scenario: Delete a user
    Given a user:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |
    Given a user:
      | username  | superman |
      | firstname | Clark    |
      | lastname  | Kent     |
    When I send DELETE request to "/users/batman"
    Then response status code should be 204
    And the "user" with the following data should have been deleted:
      | username  | batman |
      | firstname | Bruce  |
      | lastname  | Wayne  |
    But the "user" with the following data should exist:
      | username  | superman |
      | firstname | Clark    |
      | lastname  | Kent     |