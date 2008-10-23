#!/bin/sh

# Compression des fichiers png
# Copyright (c) 2008 Ovensia
# GNU General Public License (GPL)

for i in $( find ./img ./modules ./templates -name '*.png' -type f )
do
    echo "Compression PNG : $i"
    export ta=`stat -c "%s" $i`
    optipng -q -o7 $i
    export tb=`stat -c "%s" $i`
    echo "Résultat : $ta => $tb"
done
