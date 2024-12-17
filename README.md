<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-FileCopyrightText: 2017 Cooperativa EITA <eita.org.br>
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# 👥🖎 LDAP Write Support

[![REUSE status](https://api.reuse.software/badge/github.com/nextcloud/ldap_write_support)](https://api.reuse.software/info/github.com/nextcloud/ldap_write_support)

Manage your LDAP directory from within Nextcloud.

![](img/screenshots/settings.png)

* 📇 **Create records:** add new users and groups
* 📛 **Update details:** display name, email address and avatars
* ⚙️ **Integrated**: works in the known Nextcloud users page
* 📜 **Templates** configure an LDAP user template LDIF once

## Installation

This app requires the LDAP backend being enabled and configured, since it is a plugin to it. Find it on the app store!

## Beware of the dog

* Due to the internal workings of Nextcloud in provisioning users and groups, the user has to meet the LDAP filter criteria upon creation. At this point of time only the username and password are known.
* When creating groups, and empty record of `groupOfNames` is created.

## 🏗 Development setup

1. ☁ Clone this app into the `apps` folder of your Nextcloud: `git clone https://github.com/nextcloud/ldap_write_support.git`
2. 👩‍💻 In the folder of the app, run the command `npm i` to install dependencies and `npm run build` to build the Javascript
3. ✅ Enable the app through the app management of your Nextcloud
4. 🎉 Partytime! Help fix [some issues](https://github.com/nextcloud/ldap_write_support/issues) and [review pull requests](https://github.com/nextcloud/ldap_write_support/pulls) 👍
