#!/bin/sh

# Compression des fichiers css/js
# Copyright (c) 2008-2016 Ovensia
# GNU General Public License (GPL)

export YUIVER='-2.4.7'

echo "COMPRESSION DU FICHIER functions.js"

rm js/functions*.*
cat js/*.js > js/functions.js
java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 js/functions.js > js/functions.pack.js
gzip -n -c js/functions.pack.js > js/functions.pack.js.gz
rm js/functions.js;

for template in $( find ./templates/backoffice/ -maxdepth 1 -type d  \( -name eyeos* -or -name ploopi* -or -name redmine* \) )
do
    if [ -d $template ]; then
        echo "COMPRESSION DU TEMPLATE $template"

        cat $template/css/base_*.css > $template/css/styles.pack.css
        cat $template/css/ie_*.css > $template/css/styles_ie.pack.css

        for i in $( find $template \( -name '*.pack.css' \) -type f )
        do
            echo "Compression : $i => $i.gz"
            java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip -n > $i.gz
            export ta=`stat -c "%s" $i`
            export tb=`stat -c "%s" $i.gz`
            if [ $tb -eq 20 ]
            then
                java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip -n > $i.gz
                gzip -n -c -f $i > $i.gz
                export tb=`stat -c "%s" $i.gz`
                if [ $tb -eq 20 ]
                then
                    gzip -n -c -f $i > $i.gz
                    export tb=`stat -c "%s" $i.gz`
                fi
            fi
            echo "R�sultat : $ta => $tb"
        done
    fi
done

for i in $( find ./templates/install ./templates/frontoffice ./modules \( -name '*.js' -or -name '*.css' \) -type f )
do
    echo "Compression : $i => $i.gz"
    java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip -n > $i.gz
    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    if [ $tb -eq 20 ]
    then
        java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip -n > $i.gz
        gzip -n -c -f $i > $i.gz
        export tb=`stat -c "%s" $i.gz`
        if [ $tb -eq 20 ]
        then
            gzip -n -c -f $i > $i.gz
            export tb=`stat -c "%s" $i.gz`
        fi
    fi

    echo "R�sultat : $ta => $tb"
done

exit 0;

echo "COMPRESSION DES FICHIERS js/css DE FCKEDITOR"

for i in $( find ./modules ./templates/frontoffice \( -name 'fck*.js' -or -name 'fck*.css' or -name 'ck*.js' -or -name 'ck*.css' \) -type f )
do
    echo "Compression : $i => $i.gz"

    if [ `file $i | grep -c 'UTF-8'` -eq '1' ]
        then java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip -n > $i.gz
        else java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-1 $i | gzip -n > $i.gz
    fi

    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "R�sultat : $ta => $tb"
done

for i in $( find ./lib/fckeditor \( -name '*.js' -or -name '*.css' \) -type f )
do
    echo "Compression : $i => $i.gz"
    gzip -n -c $i > $i.gz
    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "R�sultat : $ta => $tb"
done

for i in $( find ./lib/jstoolbar \( -name '*.js' -or -name '*.css' \) -type f )
do
    echo "Compression : $i => $i.gz";

    if [ `file $i | grep -c 'UTF-8'` -eq '1' ]
        then java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset UTF-8 $i | gzip -n > $i.gz
        else java -jar ../yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-1 $i | gzip -n > $i.gz
    fi

    export ta=`stat -c "%s" $i`
    export tb=`stat -c "%s" $i.gz`
    echo "R�sultat : $ta => $tb"
done
