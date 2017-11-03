# user_ldap_extended
This is an experimental app. Use at your own risk!

user_ldap_extended App enables your NextCloud instance to fully work over an LDAP user base. user_ldap_extended hooks NextCloud functions of create/delete user, create/delete groups and add/remove users from groups to edit directly an LDAP user base on your desired server.

## Dependencies

In order to use `user_ldap_extended`, `user_ldap` plugin must be enabled!
user_ldap_extended will work in NC13. We at EITA Cooperative are using in a patch done to NC12, the patch is available here:  https://gitlab.com/eita/rios/rios-cloud-server/tree/rios-vivos

## Install

Place this app in **nextcloud/apps/**. From your nextcloud root:
```
cd apps/
git clone git@gitlab.com:eita/rios/user_ldap_extended.git
```
## Configure

For `user_ldap_extended` to work properly, `user_ldap` plugin should correctly configured.


## Known issues

* Usernames containing spaces do not work

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

Each user can edit displayName, avatar, street and mail using the personal profile editor on NextCloud.
