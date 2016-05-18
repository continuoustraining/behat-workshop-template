@interface
Feature: Interface tests
  As a website visitor
  I need to be able to ...

  Background:
    Given I am on "/interface-tests/page1.html"

  Scenario: Check content of page1
    Then I should see "This is the body of page 1."

  Scenario: Redirect to Disney when selecting it in the Goto Select
    When I select "Disney" from "goto"
    Then I should see "Disney"
    But I should not see "CNN"