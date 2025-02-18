#!/bin/sh
#
# SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
# SPDX-License-Identifier: AGPL-3.0-or-later

# Stop at first error
set -e

# Use version from changelog
# version=$(head -n1 CHANGELOG.md|cut -d"v" -f2);
version=$(grep '^# ' CHANGELOG.md|head -n1|cut -d' ' -f2|cut -d' ' -f1);
# version=$1
# The target branch, defaults to the current branch
target=${2:-$(git branch --show-current)}

if [ $(git branch --show-current) != $target ]; then
    if ! git switch $target > /dev/null; then
        echo "Target branch does not exist, please enter a valid branch name"
        exit 1
    fi
fi

echo "Releasing version $version on branch $target";

# Ask for confirmation
read -r -p "Are you sure? [y/N] " input

case $input in
    [yY][eE][sS]|[yY])
        echo "You say Yes"
        ;;
    [nN][oO]|[nN])
        echo "You say No"
        exit 1
        ;;
    *)
        echo "Invalid input..."
        exit 1
        ;;
esac

# Ask for confirmation
read -r -p "Create commit and bump package.json version? [y/N] " input

case $input in
    [yY][eE][sS]|[yY])
        echo "You say Yes"
        # Bump version in info.xml
        sed -i -E "s|^    <version>.+</version>|    <version>$version</version>|" appinfo/info.xml

        # Add changed files to git
        git add CHANGELOG.md
        git add appinfo/info.xml

        # Bump npm version, commit and tag
        npm version --allow-same-version -f $version

        # Show the result
        git log -1 -p

        # Add signoff
        git commit --amend -s
        ;;
    *)
        echo "You say No"
        ;;
esac

# Ask for confirmation
read -r -p "Push? [y/N] " input

case $input in
    [yY][eE][sS]|[yY])
        echo "You say Yes"
        # Then:
        git push --tags
        # Create release on github

        git push git@github.com:nextcloud-releases/ldap_write_support.git v$version
        # Create release on github
        ;;
    *)
        echo "You say No"
        ;;
esac

# Then manually:
echo "Create release on github from tag on https://github.com/nextcloud/ldap_write_support/tags"
echo "Create release on github from tag on https://github.com/nextcloud-releases/ldap_write_support/tags"
