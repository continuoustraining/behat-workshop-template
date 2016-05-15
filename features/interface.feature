@interface
Feature: Interface tests
  As a website visitor
  I need to be able to ...

  Background:
    Given I am on "/interface-tests/page1.html"

  Scenario:
    Then I should see "This is the body of page 1."