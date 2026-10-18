#!/bin/sh

# Normalisation des sources : tabulations -> 4 espaces, espaces en fin de ligne,
# lignes vides en début/fin de fichier et lignes vides consécutives.
# Usage : tools/clean.sh [dossier...]   (par défaut : tout le dépôt hors vendor/ et data/)
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

cd "$(dirname "$0")/.."
[ $# -eq 0 ] && set -- .

for i in $(find "$@" \( -path ./vendor -o -path ./data -o -path ./.git \) -prune -o \( -name '*.php' -o -name '*.js' -o -name '*.css' -o -name '*.tpl' -o -name '*.xml' \) -type f -print)
do
    echo "$i"
    expand -t4 "$i" > "$i.bak" && mv "$i.bak" "$i"
    sed 's/[ \t]*$//' "$i" > "$i.bak" && mv "$i.bak" "$i"
    sed '/./,/^$/!d' "$i" > "$i.bak" && mv "$i.bak" "$i"
    sed '/^$/N;/\n$/D' "$i" > "$i.bak" && mv "$i.bak" "$i"
done
