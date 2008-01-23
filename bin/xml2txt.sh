#!/bin/sh
# xml2txt - Copyright 2007 stephane at escaich.fr
# Absolutely no warranty, provided as-is, use at own risk.

sed -e 's/<[^>]*>//g;s/&lt;/</g;s/&gt;/>/g;s/&apos;/'"'"'/g;s/&quot;/"/g;s/&amp;/\&/g' "$1" | fmt -s | cat -s
