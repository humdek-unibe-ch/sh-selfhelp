#!/bin/sh
set -e

PROGNAME=$(basename $0)

die() {
    echo "$PROGNAME: $*" >&2
    exit 1
}

usage() {
    if [ "$*" != "" ] ; then
        echo "Error: $*"
    fi

    cat << EOF

Usage: $PROGNAME [OPTION ...]
Update the SelfHelp Repository to the latest version by checking out the latest
release and applying the DB update scripts incrementally.

Options:
-h, --help          display this usage message and exit
-p, --path [PATH]   the path to the root directory of the SelfHelp project

EOF

    exit 1
}

root="./"
while [ $# -gt 0 ] ; do
    case "$1" in
        -h|--help)
            usage
            ;;
        -p|--path)
            root="$2"
            shift
            ;;
        -*)
            usage "Unknown option '$1'"
            ;;
    esac
    shift
done

echo "Changing directory to $root"
cd $root
version=$(git describe --tags)

echo "Current version: $version"
echo "Pulling from server..."

git fetch
tags=$(git tag | grep ^v | sort -V -r)
set $tags
new_version=$1

echo "Latest version: $new_version"
echo "Updating source code"

git checkout $new_version

db_updates=$(ls server/db | grep ^update_v | sort -V | awk -v version="$version" '$0 >= "update_"version' )

echo "apply the following db update scripts:"
for file in $db_updates
do
    echo "- $rootserver/db/$file"
done
