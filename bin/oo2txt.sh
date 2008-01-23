#!/bin/sh
# sxw2text - Copyright 2004 MJ Ray mjr at dsl.pipex.com
# I grant you permission to do any act restricted by copyright with this file
# Treat it like PD. I don't care about these 7 shell lines.
# A voluntary credit, a link and an email would be cool.
# Absolutely no warranty, provided as-is, use at own risk.
#
# Modified by stephane at ovensia.fr

if [ -e content.xml ] ; then
  echo Not overwriting content.xml in current directory >&2
  exit 127
fi

export DEST=`date +%s%N`

unzip -o -qq "$1" content.xml -d /tmp/$DEST 1>&2 && \
sed -e 's/<[^>]*>/\
/g;s/&lt;/</g;s/&gt;/>/g;s/&apos;/'"'"'/g;s/&quot;/"/g;s/&amp;/\&/g' /tmp/$DEST/content.xml \
 | fmt -s | cat -s
rm /tmp/$DEST/content.xml
rmdir /tmp/$DEST
