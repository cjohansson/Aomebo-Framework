#!/bin/sh
# This script updates aomebo-framework via GIT
dir="$(dirname $(readlink -f "$0"))"
command="cd ${dir} && /usr/bin/git pull origin master"
su -s /bin/sh root -c "${command}"
