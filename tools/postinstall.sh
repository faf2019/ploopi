#!/bin/sh

# Droits des fichiers après installation ou mise à jour de Ploopi (installation manuelle)
#
# Le code est en lecture seule pour Apache ; seuls config/, modules/ (installation des
# modules depuis le backoffice) et le dossier des documents restent inscriptibles.
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

chown -R "$OWNER" .
find . -path ./vendor -prune -o -type d -exec chmod 500 {} +
find . -path ./vendor -prune -o -type f -exec chmod 400 {} +
chmod -R u=rX vendor
for d in config modules "$DATA"; do
    [ -d "$d" ] || continue
    chown -R "$OWNER" "$d"
    find "$d" -type d -exec chmod 700 {} +
    find "$d" -type f -exec chmod 600 {} +
done
chmod 500 bin/* cli cron tools/*.sh
