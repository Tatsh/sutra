#!/bin/sh
DOCDIR="/home/tatsh/dev/sutra-doc"
FLOURISH="/home/tatsh/dev/php/flourish"
SUTRA="/home/tatsh/dev/sutra/classes"

phpdoc -title="Sutra documentation (generated $(date))" \
	-t "$DOCDIR" \
	-d "$SUTRA,$FLOURISH" \
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
