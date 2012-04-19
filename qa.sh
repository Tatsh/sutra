#!/bin/sh
CLASS="${1:-.}"
pushd classes

if [[ $CLASS != '.' && "${CLASS:(-4)}" != ".php" ]]; then
	CLASS="$CLASS.php"
fi

phpmd "$CLASS" text ../phpmd-ruleset.xml
popd

# kate: replace-tabs false;
