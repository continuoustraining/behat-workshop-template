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
    And response report should contain the entry:
      | username  | pascal |
      | firstname | Pascal |
      | lastname  | Paulis |
    And response report should contain the entry:
      | username  | frederic |
      | firstname | Frédéric |
      | lastname  | Dewinne  |