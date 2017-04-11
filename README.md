# LdapUserManagement
This is an experimental app. Use at your own risk!

LdapUserManagement App enables your NextCloud instance to fully work over an LDAP user base. LdapUserManagement modifies NextCloud functions of create/delete user, create/delete groups and add/remove users from groups to edit directly an LDAP user base on your desired server.

## Dependencies

In order to use LdapUserManagement, `ldap_user` plugin must be enabled!

## Install

Place this app in **nextcloud/apps/**. From your nextcloud root:
```
cd apps/
git clone git@gitlab.com:eita/ldapusermanagement.git
```
## Configure

For LdapUserManagement to work properly, the following configurations at user_ldap should work be correctly set :

* Host (ex: localhost)
* Port (ex: 389)
* DN (ex: cn=admin,dc=localhost)
* Password
* Advanced > User Base (ou=users,dc=localhost)
* Advanced > Group Base (ou=groups,dc=localhost)

## Known issues

* Usernames containing spaces do not work
* For this moment, each new user displays twice on user list. Solving this issue depends on a patch to user_ldap, which will be done ASAP.

## LDAP parameters

NextCloud interface for creating users allows only to input an username and a password. However, other parameters must be given so that LDAP can create an user an NextCloud can recognize it. Parameters and values are not configurable by now, and are fixed at:
            
* o: uid
* objectClass: {'inetOrgPerson', 'posixAccount', 'top'}
* cn: uid
* gidnumber: 
* homedirectory: 
* mail: 'x@x.com'
* sn: uid
* uid: uid
* uidnumber: 1010
* userpassword: password
* displayName: uid
* street: 'street'

Each user can edit displayName, street and mail using the personal profile editor on NextCloud.
