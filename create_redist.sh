#!/bin/sh

# Pour l'export SQL :
# mysqldump -uroot -p<PASS> --opt --default-character-set=latin1 --comments=FALSE ploopixxxx > install/system/ploopi.sql

export DEST=ploopi_1.10.0.0

#################################
# compression des fichiers
#################################
# ./compress.sh

#################################
# on cree le dossier
#################################
rm -i -rf $DEST
rm -i -rf $DEST.tgz

echo "create redist in $DEST"

mkdir $DEST

cp *.php $DEST
cp composer.json $DEST
cp .htaccess_modele $DEST/.htaccess
cp cron $DEST
cp cli $DEST

cp -r bin $DEST

mkdir -p $DEST/data/shared
cp data/index.html $DEST/data

mkdir $DEST/doc
cp doc/{CHANGELOG,COPYRIGHT,FLOSS,INSTALL,LICENSE} $DEST/doc

cp -r config $DEST
cp -r img $DEST
cp -r include $DEST
cp -r js $DEST
cp -r lib $DEST
rm $DEST/config/config.php

mkdir $DEST/install
cp -r install/booking $DEST/install
cp -r install/dbreport $DEST/install
cp -r install/directory $DEST/install
cp -r install/doc $DEST/install
cp -r install/forms $DEST/install
cp -r install/planning $DEST/install
cp -r install/webedit $DEST/install
cp -r install/wiki $DEST/install
cp -r install/system $DEST/install
cp install/index.html $DEST/install

cp -r lang $DEST
cp -r lib $DEST

mkdir $DEST/modules
cp -r modules/system $DEST/modules
cp modules/index.html $DEST/modules

mkdir -p $DEST/templates/{frontoffice,backoffice}
#cp templates/*.php $DEST/templates
cp -r templates/frontoffice/andreas07 $DEST/templates/frontoffice
cp -r templates/frontoffice/exemple* $DEST/templates/frontoffice
cp -r templates/frontoffice/ovensia $DEST/templates/frontoffice
cp -r templates/frontoffice/ovensia_officiel $DEST/templates/frontoffice
cp -r templates/frontoffice/default $DEST/templates/frontoffice
cp -r templates/backoffice/ploopi2 $DEST/templates/backoffice
cp -r templates/install $DEST/templates/

cd $DEST

#############
# nettoyage #
#############

find . -name ".svn" -print0 | xargs -0 -n 1 rm -rf
find . -name "*.*~" -print0 | xargs -0 -n 1 rm -rf
find . -name "Thumbs.db" -print0 | xargs -0 -n 1 rm -rf

#################################
# on remet a plat les droits
#################################

chown -R www-data:www-data .

find . -type d -print0 | xargs -0 -n 1 chmod 550
find . -type f -print0 | xargs -0 -n 1 chmod 440

find {data,config,modules} -type d -print0 | xargs -0 -n 1 chmod 770
find {data,config,modules} -type f -print0 | xargs -0 -n 1 chmod 660

find bin -type f -print0 | xargs -0 -n 1 chmod 550
chmod 550 ./cgi/upload.cgi

chmod 550 *.sh

cd ..

#################################
# on fait un petit paquet
#################################

tar jcvf $DEST.tar.bz2 $DEST

# Hash SHA1
sha1sum $DEST.tar.bz2 > $DEST.tar.bz2.shasum
