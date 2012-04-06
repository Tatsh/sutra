#!/bin/sh
mkdir -p template

chmod 0700 non-writable-directory &> /dev/null
rmdir non-writable-directory &> /dev/null

phpunit --stderr -c config.xml .

chmod 0700 non-writable-directory &> /dev/null
rmdir non-writable-directory &> /dev/null

rm -rf flourish__*

echo

