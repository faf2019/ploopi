#!/bin/sh
export DEST=ploopi_1.0RC3
export YUIVER='-2.3.5'

#################################
# on cree le dossier
#################################
rm -i -rf $DEST
rm -i -rf $DEST.tgz

echo "create redist in $DEST"

mkdir $DEST

cp *.php $DEST
cp .htaccess_modele $DEST/.htaccess
cp ploopi_*.txt $DEST

cp -r bin $DEST

mkdir $DEST/data
cp data/index.html $DEST/data

cp -r cgi $DEST
cp -r config $DEST
cp -r db $DEST
cp -r FCKeditor $DEST
cp -r img $DEST
cp -r include $DEST
cp -r js $DEST
cp -r lib $DEST
rm $DEST/config/config.php

mkdir $DEST/install
#cp -r install/agenda $DEST/install
cp -r install/chat $DEST/install
cp -r install/directory $DEST/install
cp -r install/doc $DEST/install
cp -r install/forms $DEST/install
cp -r install/news $DEST/install
cp -r install/rss $DEST/install
cp -r install/webedit $DEST/install
cp -r install/system $DEST/install
cp install/index.html $DEST/install

cp -r lang $DEST
cp -r lib $DEST

mkdir $DEST/modules
cp -r modules/system $DEST/modules
cp modules/index.html $DEST/modules

mkdir -p $DEST/templates/{frontoffice,backoffice}
#cp templates/*.php $DEST/templates
cp -r templates/frontoffice/exemple* $DEST/templates/frontoffice
cp -r templates/frontoffice/ovensia $DEST/templates/frontoffice
cp -r templates/frontoffice/ovensia_officiel $DEST/templates/frontoffice
cp -r templates/frontoffice/default $DEST/templates/frontoffice
cp -r templates/backoffice/ploopi $DEST/templates/backoffice
cp -r templates/backoffice/ploopi_menus $DEST/templates/backoffice
cp -r templates/install $DEST/templates/

#############
# nettoyage #
#############

find $DEST -name .svn -print0 | xargs -0 rm -rf
find $DEST -name "*.*~" | xargs rm
find $DEST -name "Thumbs.db" | xargs rm

####################
# compression js
####################

java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 js/functions.pack.js > $DEST/js/functions.pack.js

####################
# compression css
####################

for template in {$DEST/templates/backoffice/ploopi*}; do
    if [ -d $template ]; then

        cat $template/css/base_*.css > $template/css/styles_unpack.css
        cat $template/css/ie_*.css > $template/css/styles_ie_unpack.css

        java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $template/css/styles_unpack.css > $template/css/styles.css
        java -jar yuicompressor/build/yuicompressor$YUIVER.jar --charset ISO-8859-15 $template/css/styles_ie_unpack.css > $template/css/styles_ie.css

        rm -rf $template/css/*unpack*

    fi
done

cd $DEST

#################################
# on remet a plat les droits
#################################

chown -R www-data:www-data .

find . -type d | xargs chmod 550
find . -type f | xargs chmod 440

find {data,config,modules} -type d | xargs chmod 770
find {data,config,modules} -type f | xargs chmod 660

find bin -type f | xargs chmod 550
chmod 550 ./cgi/upload.cgi

cd ..

#################################
# on fait un petit paquet
#################################

tar jcvf $DEST.tar.bz2 $DEST

# Hash SHA1
sha1sum $DEST.tar.bz2 > $DEST.tar.bz2.shasum
