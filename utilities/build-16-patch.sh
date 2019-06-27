#!/bin/bash

LATEST_16_RELEASE=${LATEST_16_RELEASE:=v16.0.1}
PATH_STABLE16=${PATH_STABLE16:=/srv/http/nextcloud/stable16}
PATH_LDAP_WRITE_SUPPORT=${PATH_LDAP_WRITE_SUPPORT:=$PWD}

cd "PATH_STABLE16"
git checkout "$LATEST_16_RELEASE"

# Changes not present in 16.0.1
https://patch-diff.githubusercontent.com/raw/nextcloud/server/pull/16000.patch -O 16000.patch
patch -p1 < 16000.patch
rm 16000.patch

wget https://patch-diff.githubusercontent.com/raw/nextcloud/server/pull/16015.patch -O 16015.patch
patch -p1 < 16015.patch
rm 16015.patch

wget https://patch-diff.githubusercontent.com/raw/nextcloud/server/pull/16112.patch -O 16112.patch
patch -p1 < 16112.patch
rm 16112.patch

wget https://github.com/nextcloud/server/commit/17bc99743b142aa04dc85d64ac0f606467800f51.patch -O 17bc997.patch
patch -p1 < 17bc997.patch
rm 17bc997.patch

# Changes in Nextcloud 17
patch -p1 < user-creation-options.patch

npm i
npm run build

git diff --stat --name-only  | grep -v '^package-lock' | grep -v '^apps/user_ldap/tests/' | grep -v '^apps/accessibility' | grep -v '^apps/files_sharing' | grep -v '^apps/oauth2' | grep -v '^apps/provisioning_api/tests' | grep -v '^apps/twofactor_backupcodes' | grep -v '^settings/js/vue-settings-admin-security' | grep -v '^settings/src/' | grep -v '^settings/js/vue-settings-personal-security'  | grep -v 3rdparty | xargs git diff --full-index --binary --stat
git diff --stat --name-only  | grep -v '^package-lock' | grep -v '^apps/user_ldap/tests/' | grep -v '^apps/accessibility' | grep -v '^apps/files_sharing' | grep -v '^apps/oauth2' | grep -v '^apps/provisioning_api/tests' | grep -v '^apps/twofactor_backupcodes' | grep -v '^settings/js/vue-settings-admin-security' | grep -v '^settings/src/' | grep -v '^settings/js/vue-settings-personal-security'  | grep -v 3rdparty | xargs git diff --full-index --binary > "$PATH_LDAP_WRITE_SUPPORT/ldap_write_support-$LATEST_16_RELEASE.patch"
