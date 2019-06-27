# 👥🖎 LDAP Write Support

Manage your LDAP directory from within Nextcloud.

![](screenshots/settings.png)

* 📇 **Create records:** add new users and groups
* 📛 **Update details:** display name, email address and avatars
* ⚙️ **Integrated**: works in the known Nextcloud users page
* 📜 **Templates** configure an LDAP user template LDIF once

## Installation

This app requires the LDAP backend being enabled and configured, since it is a plugin to it. Find it on the app store!

For Nextcloud 16.0.1 and also to use all features in Nextcloud 16, following patch has to be applied:

```
wget https://github.com/nextcloud/ldap_write_support/releases/download/untagged-fea033b1ad3a244b3cd3/ldap_write_support-v16.0.1.patch
git apply -p1 < /path/to/ldap_write_support-v16.0.1.patch
```

`git` is required for applying the patch, because it contains changes in binary files (compiled javascript resources) and the good old `patch`  does not have support for it.

## Known limitations

* Due to the internal workings of Nextcloud in provisioning users and groups, the user has to meet the LDAP filter criteria upon creation. At this point of time only the username and password are known.
* When creating groups, and empty record of `groupOfNames` is created.

## 🏗 Development setup

Currently this app requires the master branch of the [Viewer app](https://github.com/nextcloud/viewer).

1. ☁ Clone this app into the `apps` folder of your Nextcloud: `git clone https://github.com/nextcloud/text.git`
2. 👩‍💻 In the folder of the app, run the command `npm i` to install dependencies and `npm run build` to build the Javascript.
3. ✅ Enable the app through the app management of your Nextcloud
4. 🎉 Partytime! Help fix [some issues](https://github.com/nextcloud/ldap_write_supprt/issues) and [review pull requests](https://github.com/nextcloud/ldap_write_support/pulls) 👍
