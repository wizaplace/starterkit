Feature: CMS Pages

  Scenario: Visit the FAQ page
    When I go to "/faq"
    Then the page meta title should be "FAQ - Starter Kit"
    And the page top title should be "FAQ"
    And the page meta description should contain "Foire Aux Questions"
