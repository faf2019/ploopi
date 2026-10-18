#!/bin/sh

# Vérification syntaxique (php -l) des fichiers PHP du dépôt, hors vendor/ et data/
# N'affiche que les erreurs ; code de retour non nul si au moins un fichier échoue.
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

cd "$(dirname "$0")/.."

ret=0
for f in $(find . \( -path ./vendor -o -path ./data -o -path ./.git \) -prune -o -name '*.php' -type f -print)
do
    if ! out=$(php -l "$f" 2>&1); then
        echo "$out"
        ret=1
    fi
done
exit $ret
