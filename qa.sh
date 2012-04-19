#!/bin/sh
CLASS="${1:-.}"
pushd classes
phpmd "$CLASS" text ../phpmd-ruleset.xml
popd

