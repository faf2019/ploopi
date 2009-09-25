#!/bin/sh

# Compression des fichiers css/js
# Copyright (c) 2008-2009 Ovensia
# GNU General Public License (GPL)

export YUIVER='-2.4.2'

echo "COMPRESSION DU FICHIER functions.js"

rm js/functions*.*
cat js/*.js > js/functions.js
java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 js/functions.js > js/functions.pack.js 
gzip -c js/functions.pack.js > js/functions.pack.js.gz
rm js/functions.js;

for template in ./templates/backoffice/{eyeos,redmine,ploopi*}; do
    if [ -d $template ]; then
        echo "COMPRESSION DU TEMPLATE $template"

        cat $template/css/base_*.css > $template/css/styles.pack.css
        cat $template/css/ie_*.css > $template/css/styles_ie.pack.css

        for i in $( find $template \( -name '*.pack.css' \) -type f )
        do
            echo "Compression : $i => $i.gz"
            java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $i | gzip > $i.gz
            export ta=`stat -c "%s" $i`
            export tb=`stat -c "%s" $i.gz`
            echo "Résultat : $ta => $tb"
        done
    fi
done

echo "COMPRESSION DES FICHIERS js/css DES MODULES"

for i in $( find ./modules ./templates/frontoffice \( \( -name '*.js' -or -name '*.css' \) -and -not -name 'fck*' \) -type f )
do
    echo "Compression : $i => $i.gz"
    java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $i | gzip > $i.gz
    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "Résultat : $ta => $tb"
done

echo "COMPRESSION DES FICHIERS js/css DE FCKEDITOR"

for i in $( find ./modules ./templates/frontoffice \( -name 'fck*.js' -or -name 'fck*.css' \) -type f )
do
    echo "Compression : $i => $i.gz"
    java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip > $i.gz
    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "Résultat : $ta => $tb"
done

for i in $( find ./lib/fckeditor \( -name '*.js' -or -name '*.css' \) -type f )
do
    echo "Compression : $i => $i.gz"
    gzip -c $i > $i.gz
    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "Résultat : $ta => $tb"
done


for i in $( find ./lib/jstoolbar \( -name '*.js' -or -name '*.css' \) -type f )
do
    echo "Compression : $i => $i.gz"
    java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip > $i.gz
    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "Résultat : $ta => $tb"
done
