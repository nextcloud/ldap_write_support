Feature: user

  Background:
    Given using api version "2"
    And having a valid LDAP configuration
    And modify LDAP configuration
      | ldapBaseUsers  | ou=PagingTest,dc=nextcloud,dc=ci |

  Scenario: create a new user
    Given As an "admin"
    And user "brand-new-user" does not exist
    When creating a user with
      | userid | brand-new-user |
      | password | 123456 |
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And user "brand-new-user" exists
    And invoking occ with "user:info brand-new-user"
    And the command output contains the text "backend: LDAP"

  # requires NC 17
  Scenario: create a new user with dynamic user id
    Given As an "admin"
    And parameter "newUser.generateUserID" of app "core" is set to "yes"
    When creating a user with
      | userid |  |
      | password | 123456 |
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"

  # requires NC 17
  Scenario: create a new user with dynamic user id
    Given As an "admin"
    And parameter "newUser.generateUserID" of app "core" is set to "yes"
    And creating a user with
      | userid |  |
      | password | 123456 |
      | displayName | Foo B. Ar |
    And the OCS status code should be "200"
    And the HTTP status code should be "200"
    When sending "GET" to "/cloud/users?search=Foo"
    Then it yields "1" result


  # requires NC 17
  Scenario: create a new user with dynamic user id and required email
    Given As an "admin"
    And parameter "newUser.generateUserID" of app "core" is set to "yes"
    And parameter "newUser.requireEmail" of app "core" is set to "yes"
    When creating a user with
      | userid |  |
      | password | 123456 |
      | email    | foo@bar.foobar |
    Then the OCS status code should be "101"
    And the HTTP status code should be "400"
    # this is how we end up when the welcome email cannot be sent - wrong OCS code is a server thing

  # requires NC 17
  Scenario: create a new user with dynamic user id, forgot email
    Given As an "admin"
    And parameter "newUser.generateUserID" of app "core" is set to "yes"
    And parameter "newUser.requireEmail" of app "core" is set to "yes"
    When creating a user with
      | userid |  |
      | password | 123456 |
    Then the OCS status code should be "110"
    And the HTTP status code should be "400"

