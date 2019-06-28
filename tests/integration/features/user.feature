Feature: user
  | ldapBaseGroups                | ou=OtherGroups,dc=nextcloud,dc=ci |

  Background:
    Given using api version "2"
    And having a valid LDAP configuration
    And modify LDAP configuration
      | ldapBaseUsers                 | ou=PagingTest,dc=nextcloud,dc=ci |
      | ldapBaseGroups                | ou=OtherGroups,dc=nextcloud,dc=ci |
      | ldapGroupMemberAssocAttr      | member |
      | ldapGroupFilter               | objectclass=groupOfNames |
      | useMemberOfToDetectMembership | 1 |

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
    And the created users resides on LDAP

  # requires NC 17
  Scenario: create a new user with dynamic user id without user base set
    Given As an "admin"
    And parameter "newUser.generateUserID" of app "core" is set to "yes"
    And modify LDAP configuration
      | ldapBaseUsers |  |
    When creating a user with
      | userid |  |
      | password | 123456 |
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And the created users resides on LDAP

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
    And the created users resides on LDAP
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
    Then the OCS status code should be "109"
    And the HTTP status code should be "400"
    # because we cannot send email here, we'll get this error === success

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

  Scenario: as subadmin create a user with an assigned group
    Given As an "admin"
    And creating a group with gid "working-group"
    And creating a user with
      | userid | subadmin |
      | password | 123456 |
      | groups[]   | working-group |
      | subadmin[] | working-group |
    And the created users resides on LDAP
    When As an "subadmin"
    And creating a user with
      | userid | regular-user |
      | password | 123456     |
      | groups[]   | working-group |
    Then the OCS status code should be "200"
    And the HTTP status code should be "200"
    And the created users resides on LDAP
    And As an "admin"
    And check that user "regular-user" belongs to group "working-group"

