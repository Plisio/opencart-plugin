#!/bin/sh

rm -f ./plisio-opencart-156-002.zip
find . -name 'plisio*' -print | zip plisio-opencart-156-002.zip -@
zip plisio-opencart-156-002.zip upload/system/library/plisio/*