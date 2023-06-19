#!/bin/bash
# 执行安全脚本 sh security.sh
base=$(dirname $(realpath ${BASH_SOURCE}))
chown www:www $base -R
chmod 555 $base -R
chmod u+w ${base}"/runtime" -R
chmod u+w ${base}"/public/upload" -R