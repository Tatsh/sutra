#!/bin/sh
DOCDIR="/home/tatsh/dev/sutra-doc"
FLOURISH="/home/tatsh/dev/flourish"
SUTRA="/home/tatsh/dev/sutra-separated/classes"

phpdoc -ti "Sutra documentation (generated $(date)" \
	-t "$DOCDIR" \
	-d "$FLOURISH,$SUTRA"
pushd "$DOCDIR"
git commit -m "Generated documentation" -a
git push -u origin gh-pages
popd
