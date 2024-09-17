# 1.12.0 - 20 Aug 2024

- Support for Nextcloud 30

# 1.11.0 - 07 Mar 2024

- Support Nextcloud 29
- Migrated to vite

# 1.10.0 - 19 Dec 2023

Compatibility with Nextcloud 28

# 1.9.0 - 15 May 2023

Compatibility with Nextcloud 27

# 1.8.0 - 20 Apr 2023

- Nextcloud 26 support

# 1.7.0 - 02 Nov 2022

* Nextcloud 25 support
* Use LDAP passwd exop to set password of new users nextcloud/ldap_write_support#503
* Modernize javascript code nextcloud/ldap_write_support#519
* supply uri to ldap_connect nextcloud/ldap_write_support#524
* PHP 8.1 support nextcloud/ldap_write_support#517

# 1.6.0 - 02 Nov 2022

* Nextcloud 24 support
* Modernize javascript code [#520](https://github.com/nextcloud/ldap_write_support/issues/520)
* supply uri to ldap_connect [#525](https://github.com/nextcloud/ldap_write_support/issues/525)
* PHP 8.1 support [#518](https://github.com/nextcloud/ldap_write_support/issues/518)

# 1.4.0 - 15 Jul 2021

https://github.com/nextcloud-releases/ldap_write_support/releases/tag/v1.4.0

# 1.3.0 - 29 Jan 2021

* Nextcloud 21 support

# 1.2.1 - 29 Jan 2021

* dependency updates

# 1.2.0 - 13 Nov 2020

* [ldap_write_support#144](https://github.com/nextcloud/ldap_write_support/pull/144) Fix account creation with the registration app
* [ldap_write_support#146](https://github.com/nextcloud/ldap_write_support/pull/146) Fix new account template
* [ldap_write_support#165](https://github.com/nextcloud/ldap_write_support/pull/165) Update tests and bump max version
* [ldap_write_support#178](https://github.com/nextcloud/ldap_write_support/pull/178) Do not use custom DI object names for user_ldap
* [ldap_write_support#189](https://github.com/nextcloud/ldap_write_support/pull/189) Update LDAPUserManager.php error in split with ':'
* [ldap_write_support#190](https://github.com/nextcloud/ldap_write_support/pull/190) Do not trigger loading of user_ldap if it is alreay loaded
* [ldap_write_support#205](https://github.com/nextcloud/ldap_write_support/pull/205) Implements ibootstrap and cleans up code
* [ldap_write_support#234](https://github.com/nextcloud/ldap_write_support/pull/234) Works around calling occ issue on integration tests by relying on APi
* depndency updates

# 1.1.0 - 17 Jan 2020

## Added

* Nextcloud 18 compatibility

## Changed

* ensure app is instantiated just once [#72](https://github.com/nextcloud/ldap_write_support/issues/72)
* updated dependencies

# 1.0.2 - 19 Jul 2019

## Added

*  use password exop (for NC 16) when it makes sense [#27](https://github.com/nextcloud/ldap_write_support/issues/27)

## Fixed

*  does not execute any group actions when they are disabled by backend [#28](https://github.com/nextcloud/ldap_write_support/issues/28)

# 1.0.1 - 28 Jun 2019

## Fixes

* do not log success as error, fixes [#3](https://github.com/nextcloud/ldap_write_support/issues/3) 
* clear error message when acting admin is not from LDAP despite requirement
* fallback to the general base if user or group base was not set. Fixes [#4](https://github.com/nextcloud/ldap_write_support/issues/4)

# 1.0.0 - 27 Jun 2019

[v1.12.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.11.0...v1.12.0
[v1.11.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.10.0...v1.11.0
[v1.10.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.9.0...v1.10.0
[v1.9.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.8.0...v1.9.0
[v1.8.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.7.0...v1.8.0
[v1.7.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.6.0...v1.7.0
[v1.6.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.4.0...v1.6.0
[v1.4.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.3.0...v1.4.0
[v1.3.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.2.1...v1.3.0
[v1.2.1]: https://github.com/nextcloud/ldap_write_support/compare/v1.2.0...v1.2.1
[v1.2.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.1.0...v1.2.0
[v1.1.0]: https://github.com/nextcloud/ldap_write_support/compare/v1.0.2...v1.1.0
[v1.0.2]: https://github.com/nextcloud/ldap_write_support/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/nextcloud/ldap_write_support/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/nextcloud/ldap_write_support/tree/v1.0.0
