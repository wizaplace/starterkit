Feature: Authentication

  Scenario: Users are forced to login to see the profile page
    Given I am not logged in
    When I go to "/profil"
    Then I should be on "/login"
    And the response status code should be 200

  Scenario: Users can see the profile page when logged in
    Given I am logged in as "user@wizaplace.com" with password "password"
    When I go to "/profil"
    Then I should be on "/profil"
    And the response status code should be 200
    And the response should contain "user@wizaplace.com"
