#!/bin/sh
DOCDIR="/home/tatsh/dev/sutra-doc"
FLOURISH="/home/tatsh/dev/flourish"
SUTRA="/home/tatsh/dev/sutra-separated/classes"

phpdoc -ti "Sutra documentation (generated $(date))" \
	-t "$DOCDIR" \
	-d "$SUTRA,$FLOURISH"
pushd "$DOCDIR"
git add .
git commit -m "Generated documentation"
git push -u origin gh-pages
popd
