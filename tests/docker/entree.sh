#!/bin/bash
#
# Exécuté dans le conteneur de test par tests/docker.sh : prépare le dépôt monté, puis
# lance tests/instance.sh test (arguments transmis, --navigateur compris).
#
# Le conteneur voit le dépôt en /ploopi, mais config/, data/ et modules/ y sont des
# dossiers temporaires propres au conteneur, et vendor/ un volume Docker : un
# config/config.php, un vendor/ ou des copies déployées dans modules/ ne sont ni lus ni
# modifiés, et les données de l'instance de test ne laissent aucune trace. config/ est
# recopié depuis /ploopi-config (le config/ du dépôt, en lecture seule), sans
# config.php ; modules/ ne reçoit que system (versionné) : les suites déploient les
# autres modules depuis install/, jamais depuis une copie ancienne de l'hôte.
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

set -u
cd /ploopi || exit 2

cp -a /ploopi-config/. config/
rm -f config/config.php
cp -a /ploopi-modules/system /ploopi-modules/index.html modules/

echo "PHP $(php -r 'echo PHP_VERSION;') — dépendances Composer…"
# vendor/ est un volume propre au conteneur (tests/docker.sh) : celui du dépôt n'est ni
# lu ni modifié, quel que soit son état (paquets installés depuis les sources, modifiés…)
composer install --no-interaction --no-progress --ignore-platform-req=ext-memcached || exit 2

for i in $(seq 1 60); do
    mariadb-admin ping -h "$PLOOPI_TEST_DB_HOST" -u"$PLOOPI_TEST_DB_ADMIN" -p"$PLOOPI_TEST_DB_ADMINPWD" --silent 2>/dev/null && break
    [ "$i" -eq 60 ] && { echo "MariaDB ne répond pas." >&2; exit 2; }
    sleep 1
done

tests/instance.sh test "$@"
code=$?

# Failles et paquets abandonnés des dépendances (badge du README, tests/docker.sh --publier)
version="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
mkdir -p "tests/resultats/${version}"
composer audit --locked --format=json --no-interaction > "tests/resultats/${version}/composer-audit.json" 2>/dev/null
# Résumé lu par tests/docker.sh (l'hôte n'a ni PHP ni jq) : « <couleur> <message> »
php -r '
    $a = json_decode((string) file_get_contents($argv[1]), true);
    if (!is_array($a)) exit;
    $f = array_sum(array_map("count", (array) ($a["advisories"] ?? [])));
    $n = count((array) ($a["abandoned"] ?? []));
    if ($f) echo "red {$f} faille(s)";
    elseif ($n) echo "yellow aucune faille · {$n} abandonné(s)";
    else echo "brightgreen aucune faille";
' "tests/resultats/${version}/composer-audit.json" > "tests/resultats/${version}/dependances.txt"

# Fichiers écrits dans le dépôt monté : rendus à l'utilisateur de l'hôte
[ -n "${HOTE_UID:-}" ] && chown -R "${HOTE_UID}:${HOTE_GID:-$HOTE_UID}" tests/resultats 2>/dev/null

exit $code
