Feature: Backend Login
  In order to access the Neos backend
  As a user of the system
  I need a way to authenticate

  Scenario: Show login form for not authenticated user
    Given I am not authenticated
    When I go to "/neos"
    Then I should see a login form

  @fixtures
  Scenario Outline: Login to backend with different roles
    Given I imported the site "TYPO3.NeosDemoTypo3Org"
    And I am not authenticated
    And the following users exist:
      | username | password | firstname | lastname | roles   |
      | jdoe     | password | John      | Doe      | <roles> |
    When I go to "/neos"
    And I fill in "Username" with "jdoe"
    And I fill in "Password" with "password"
    And I press "Login"
    Then I should be in the "Content" module
    And I should be logged in as "John Doe"

    Examples:
      | roles         |
      | Editor        |
      | Administrator |

  @fixtures
  Scenario: Logout from backend stays on last edited page
    Given I imported the site "TYPO3.NeosDemoTypo3Org"
    And the following users exist:
      | username | password | firstname | lastname | roles  |
      | jdoe     | password | John      | Doe      | Editor |
    And I am authenticated with "jdoe" and "password" for the backend
    When I follow "Features" in the main menu
    And I follow "Logout"
    Then I should not be logged in
    And I should see the page title "Features"
