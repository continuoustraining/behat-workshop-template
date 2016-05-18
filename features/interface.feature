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

  Scenario: Redirect to stackoverflow when selecting it in the Goto Select, taking into account an AJAX delay.
    When I select "stackoverflow" from "goto"
    Then after some time I should be on "http://stackoverflow.com/" and see "Stack Overflow"

  Scenario: Show an invisible div by clicking on a link
    When I follow "show-invisible-div"
    Then I should see "Invisible div!" in the "#invisible-div" element