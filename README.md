# LdapUserManagement
This is an experimental app. Use at your own risk!

LdapUserManagement App enables your NextCloud instance to fully work over an LDAP user base. LdapUserManagement modifies NextCloud functions of create/delete user, create/delete groups and add/remove users from groups to edit directly an LDAP user base on your desired server.

## Dependencies

In order to use LdapUserManagement, `ldap_user` plugin must be enabled!

## Install

Place this app in **nextcloud/apps/**. From your nextcloud root:
```
cd apps/
git clone git@gitlab.com:eita/LdapUserManagement.git
```
## Known issues

* Usernames containing spaces do not work
* Deleting LDAP users causes an error message, but works.
* Deleting LDAP groups causes an error message, but works.
* Adding an user to a newly created group do not work; you must reload before adding user to group
* Adding a new user to an existing group do not work; you must reload before adding user to group
* Adding a new user to a new group do not work; you must reload before adding user to group
* Deleting a newly create group do not work; nextcloud must reload before deleting a created group.