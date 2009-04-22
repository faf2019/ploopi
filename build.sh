#!/bin/sh

# Vérification des fichiers php du portail
# Copyright (c) 2008-2009 Ovensia
# GNU General Public License (GPL)

for i in $( find . -name '*.php' -type f )
do
    php -l $i | grep -v "No syntax errors"
done
