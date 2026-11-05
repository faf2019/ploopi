#!/bin/sh

# Droits des fichiers après installation ou mise à jour de Ploopi (installation manuelle)
#
# Le code appartient à root et n'est que lisible par le groupe d'Apache : le serveur web
# ne peut pas se rendre l'écriture. config/ est en lecture
# seule après l'installation ; seuls modules/ (installation des modules depuis le
# backoffice) et le dossier des documents restent à l'utilisateur d'Apache.
# Usage, en root, depuis n'importe où :
#     tools/postinstall.sh [utilisateur:groupe] [dossier_des_documents]
# Par défaut : www-data:www-data et ./data
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

set -e
cd "$(dirname "$0")/.."

OWNER="${1:-www-data:www-data}"
DATA="${2:-./data}"

GROUP="${OWNER#*:}"

chown -R "root:$GROUP" .
find . -path ./vendor -prune -o -type d -exec chmod 750 {} +
find . -path ./vendor -prune -o -type f -exec chmod 640 {} +
chmod -R u=rwX,g=rX,o= vendor
for d in modules "$DATA"; do
    [ -d "$d" ] || continue
    chown -R "$OWNER" "$d"
    find "$d" -type d -exec chmod 700 {} +
    find "$d" -type f -exec chmod 600 {} +
done
chmod 750 bin/* cli cron tools/*.sh
