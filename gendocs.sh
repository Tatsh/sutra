#!/bin/sh
DOCDIR="$HOME/sutra-doc"
SUTRA="./classes"

bin/phpdoc.php -title="Sutra documentation (generated $(date))" \
	-t "$DOCDIR" \
	-d "$SUTRA" \
	-p \
	--defaultpackagename "Sutra" \
	--title "Sutra"
rm -fR output
pushd "$DOCDIR"
git add .
git commit -m "Generated documentation"
git push -u origin gh-pages
popd

# kate: replace-tabs false;
