#!/bin/sh
mkdir -p template/default

cp -f resources/backup/* resources
rm -f resources/*_copy*

chmod 0700 non-writable-directory &> /dev/null
rmdir non-writable-directory &> /dev/null

phpunit --stderr -c config.xml .

chmod 0700 non-writable-directory &> /dev/null
rmdir non-writable-directory &> /dev/null

rm -rf flourish__* template
rm -f resources/*_copy*

echo
