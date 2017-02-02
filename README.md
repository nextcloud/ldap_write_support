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
## Configure

Go to Admin Settings / LDAP AD Integration tab. Under the user_ldap configurations, you will find LdapUserManagement configurations. These are needed to allow the app to edit you LDAP configurations:

* Host (ex: localhost)
* Port (ex: 389)
* DN (ex: cn=admin,dc=localhost)
* Password
* User Base (ou=users,dc=localhost)
* Group Base (ou=groups,dc=localhost)

## Known issues

* Usernames containing spaces do not work
* Deleting LDAP users causes an error message, but works.
* Deleting LDAP groups causes an error message, but works.
* Adding an user to a newly created group do not work; you must reload before adding user to group
* Adding a new user to an existing group do not work; you must reload before adding user to group
* Adding a new user to a new group do not work; you must reload before adding user to group
* Deleting a newly create group do not work; nextcloud must reload before deleting a created group.

## LDAP parameters

NextCloud interface for creating users allows only to input an username and a password. However, other parameters must be given so that LDAP can create an user an NextCloud can recognize it. Parameters and values are not configurable by now, and are fixed at:
            
* o: uid
* objectClass: {'inetOrgPerson', 'posixAccount', 'top'}
* cn: uid
* gidnumber: 
* homedirectory: 
* mail:
* sn: uid
* uid: uid
* uidnumber: 1010
* userpassword: password
* displayName: uid

