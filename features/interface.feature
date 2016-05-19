@interface
Feature: Interface tests
  As a website visitor
  I need to be able to ...

  Scenario: Check content of page1
    When I go to "/justice-league.html"
    Then I should see "Homepage of the Justice League"

  Scenario: Redirect to DC Comics when selecting it in the Goto Select
    When I select "DC Comics" from "goto"
    Then I should see "DC"
    But I should not see "Marvel"

  Scenario: Redirect to DC Comics when selecting it in the Goto Select
    When I select "DC Comics" from "goto"
    Then I should see "DC"
    But I should not see "Marvel"

  Scenario: Show an invisible div by clicking on a link
    When I follow "show-super-secret-content"
    Then I should see "Super secret Justice League content!" in the "#super-secret-content" element