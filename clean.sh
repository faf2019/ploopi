#!/bin/sh

for i in $( find . \( -name '*.php' -or -name '*.js' -or -name '*.css' -or -name '*.tpl' -or -name '*.xml' \) -type f )
do
    echo $i
    
    # remplace tab par 4 espaces
    expand -t4 $i > $i.bak && mv $i.bak $i
    
    # suppression des espaces/tab en fin de ligne
    sed 's/[ \t]*$//' $i > $i.bak && mv $i.bak $i
    
    # suppression des lignes vides en début/fin de fichier
    sed '/./,/^$/!d' $i > $i.bak && mv $i.bak $i
    sed '/^$/N;/\n$/D' $i > $i.bak && mv $i.bak $i
done