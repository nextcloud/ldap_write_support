#!/bin/sh

# Stop at first error
set -e

# Use version from changelog
# version=$(head -n1 CHANGELOG.md|cut -d"v" -f2);
version=$1
echo "Releasing version $version";

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
        sed -i -E "s|^\t<version>.+</version>|\t<version>$version</version>|" appinfo/info.xml

        # Add changed files to git
        # git add CHANGELOG.md
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
