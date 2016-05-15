@users
Feature: Users
  As an API consumer
  I need to be able to manage the users

  Scenario: Retrieve the list of users
    Given a user:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    Given a user:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |
    When I send GET request to "/users"
    Then response status code should be 200
    And response should be a collection of "users"
    And response collection should contain exactly 2 "users"
    And response collection "users" should contain the resource:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    And response collection "users" should contain the resource:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |

  Scenario: Retrieve a single user
    Given a user:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    Given a user:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |
    When I send GET request to "/users/frederic"
    Then response status code should be 200
    And response entity should contain the values:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |

  Scenario: Create a new user
    When I send POST request to "/users" with payload from "create-user.json"
    Then response status code should be 201
    And response entity should contain the values:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    And a "user" with the following data should have been created:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |

  Scenario: Delete a user
    Given a user:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    Given a user:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |
    When I send DELETE request to "/users/pascal"
    Then response status code should be 204
    And the "user" with the following data should have been deleted:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    But the "user" with the following data should exist:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |