#!/bin/sh

export YUIVER='-2.3.4'

echo "COMPRESSION DU FICHIER functions.js"
java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 js/functions.js > js/functions.pack.js
gzip -cf js/functions.pack.js > js/functions.pack.js.gz

for template in ./templates/backoffice/ploopi*; do
    if [ -d $template ]; then
        echo "COMPRESSION DU TEMPLATE $template"

        cat $template/css/base_*.css > $template/css/styles.unpack.css
        cat $template/css/ie_*.css > $template/css/styles_ie.unpack.css

        java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $template/css/styles.unpack.css > $template/css/styles.pack.css
        java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $template/css/styles_ie.unpack.css > $template/css/styles_ie.pack.css

        gzip -cf $template/css/styles.pack.css > $template/css/styles.pack.css.gz
        gzip -cf $template/css/styles_ie.pack.css > $template/css/styles_ie.pack.css.gz

        rm -rf $template/css/*.unpack.css
    fi
done


#!/bin/sh
for i in $( find ./FCKeditor ./modules ./templates/frontoffice \( -name '*.js' -or -name '*.css' \) -type f )
do
    gzip -cf $i > $i.gz
done
