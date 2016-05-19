@interface
Feature: Interface tests
  As a website visitor
  I need to be able to ...

  Scenario: Check content of page1
    When I go to "/justice-league.html"
    Then I should see "Homepage of the Justice League"