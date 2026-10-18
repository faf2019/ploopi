#!/bin/sh

# Régénère les fichiers agrégés et compressés servis aux navigateurs
#
#  - js/functions.pack.js       = concaténation des sources de js/ (ordre alphabétique,
#                                 aa_init.js en premier car il crée l'objet ploopi)
#  - <gabarit>/css/*.pack.css   = concaténation des base_*.css / ie_*.css du gabarit
#  - chaque *.gz existant       = gzip de sa source, s'il est désaccordé (le .htaccess sert le .gz s'il existe)
#
# Pas de minification : la suite de tests 33 vérifie que chaque .gz est exactement
# la version compressée de sa source. À lancer depuis la racine du dépôt.
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

set -e
cd "$(dirname "$0")/.."

echo "js/functions.pack.js"
for f in $(ls js/*.js | grep -v '^js/functions' | LC_ALL=C sort); do
    cat "$f"
    echo
done > js/functions.pack.js

for template in templates/backoffice/*/; do
    [ -d "${template}css" ] || continue
    if ls "${template}"css/base_*.css > /dev/null 2>&1; then
        echo "${template}css/styles.pack.css"
        cat "${template}"css/base_*.css > "${template}css/styles.pack.css"
    fi
    if ls "${template}"css/ie_*.css > /dev/null 2>&1; then
        echo "${template}css/styles_ie.pack.css"
        cat "${template}"css/ie_*.css > "${template}css/styles_ie.pack.css"
    fi
done

# Recompresse chaque .gz désaccordé de sa source (les .gz à jour ne sont pas touchés)
find js templates lib modules/system install -name '*.gz' -type f | while read -r gz; do
    src="${gz%.gz}"
    [ -f "$src" ] || continue
    if ! gzip -dc "$gz" 2> /dev/null | cmp -s - "$src"; then
        echo "$gz"
        gzip -9 -n -c "$src" > "$gz"
    fi
done

echo "Terminé : vérifier avec tests/instance.sh test 33_icons_sans_inline"
