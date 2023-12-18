#!/usr/bin/env bash

APP_INTEGRATION_DIR=$PWD
ROOT_DIR=${APP_INTEGRATION_DIR}/../../../..

# Nextcloud integration tests composer
(
    cd ${ROOT_DIR}/build/integration
    composer install
)

composer install
composer dump-autoload

${ROOT_DIR}/occ status
${ROOT_DIR}/occ app:enable user_ldap
${ROOT_DIR}/occ app:enable ldap_write_support
${ROOT_DIR}/occ app:list

export TEST_SERVER_URL="http://localhost:8080/ocs/"

${APP_INTEGRATION_DIR}/vendor/bin/behat --colors -f junit -f pretty $1 $2
RESULT=$?

exit $RESULT
