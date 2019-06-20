Feature: user

  Background:
    Given using api version "2"
    And having a valid LDAP configuration

  Scenario: create a new user
    Given As an "admin"
    And modify LDAP configuration
      | ldapBaseUsers  | ou=PagingTest,dc=nextcloud,dc=ci |
    And user "brand-new-user" does not exist
    When sending "POST" to "/cloud/users" with
      | userid | brand-new-user |
      | password | 123456 |
    Then the OCS status code should be "100"
    And the HTTP status code should be "200"
    And user "brand-new-user" exists
    And invoking occ with "user:info brand-new-user"
    And the command output contains the text "backend: LDAP"
    
