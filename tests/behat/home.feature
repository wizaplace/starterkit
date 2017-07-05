Feature: Home Page

  Scenario: Land on home page
    When I am on the homepage
    Then the response should contain "Catégorie principale"

  @javascript
  Scenario: Land on home page with JS
    When I am on the homepage
    And I click on the category "Écrans" under "Informatique" in the top menu
    Then I should be on "/c/ecrans"

