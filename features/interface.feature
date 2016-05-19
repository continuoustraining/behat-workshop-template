@interface
Feature: Interface tests
  As a website visitor
  I need to be able to ...

  Background:
    When I am on "/justice-league.html"

  Scenario: Check content of page1
    Then I should see "Homepage of the Justice League"

  Scenario: Redirect to DC Comics when selecting it in the Goto Select
    When I select "DC Comics" from "goto"
    Then I should see "DC"
    But I should not see "Marvel"

  Scenario: Show an invisible div by clicking on a link
    When I follow "show-super-secret-content"
    Then I should see "Super secret Justice League content!" in the "#super-secret-content" element

  Scenario: Fill in the login form and verify that the user is redirected to the correct page.
    When I fill in the following:
      | username | batman |
      | password | robin  |
    And I press "submitBtn"
    Then I should be on "/you-are-connected.html"
    And I should see "You are connected!"

  Scenario: Redirect to Marvel Comics when selecting it in the Goto Select, taking into account an AJAX delay.
    When I select "Marvel Comics" from "goto"
    Then after some time I should be on "http://marvel.com/comics" and see "Marvel"