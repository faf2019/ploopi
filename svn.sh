#!/bin/sh
for i in $( find . \( -name '*.php' -or -name '*.js' -or -name '*.css' -or -name '*.tpl' \) -type f )
do
#    sed "s/@author St�phane Escaich/@author St�phane Escaich\n\
# * @version  \$Revision\$\n\
# * @modifiedby \$LastChangedBy\$\n\
# * @lastmodified \$Date\$/" $i > $i.bak
#    mv $i.bak $i
    svn propset -R svn:keywords "Revision Author Date LastChangeBy" $i
done

