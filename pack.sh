#!/bin/sh
rm -f ./plisio-opencart-156-001.zip.zip
find . -name 'plisio*' -print | zip plisio-opencart-156-001.zip -@
