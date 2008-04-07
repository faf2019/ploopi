#!/bin/sh

export YUIVER='-2.3.4'

echo "COMPRESSION DU FICHIER functions.js"
java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 js/functions.js > js/functions.pack.js

for template in ./templates/backoffice/ploopi*; do
    if [ -d $template ]; then
        echo "COMPRESSION DU TEMPLATE $template"

        cat $template/css/base_*.css > $template/css/styles.unpack.css
        cat $template/css/ie_*.css > $template/css/styles_ie.unpack.css

        java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $template/css/styles.unpack.css > $template/css/styles.pack.css
        java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $template/css/styles_ie.unpack.css > $template/css/styles_ie.pack.css

        rm -rf $template/css/*.unpack.css
    fi
done


