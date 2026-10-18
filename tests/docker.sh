#!/bin/bash
#
# Lance la suite complète (tests/instance.sh test) dans un conteneur PHP de la version
# demandée, avec une base MariaDB 10.11 jetable : seul Docker est nécessaire sur la
# machine, ni PHP, ni MySQL, ni Node.
#
# Usage :
#   tests/docker.sh 8.1|8.2|8.3|8.4|8.5 [--navigateur] [--publier]
#   tests/docker.sh tout [--navigateur] [--publier]   les cinq versions, comme la matrice de la CI
#
#   --navigateur    ajoute le parcours navigateur (Playwright et Chromium dans l'image)
#   --publier       publie le résultat de chaque version comme statut du commit testé sur
#                   GitHub (contexte tests-docker/php<version>), sans minute de CI. Exige un
#                   commit poussé, aucune modification suivie, et gh connecté ou GITHUB_TOKEN
#
# Le dépôt est monté en volume : le code testé est celui de la copie de travail. Un
# config/config.php, un vendor/ ou des modules déployés dans modules/ ne sont ni lus ni
# modifiés (config/, data/ et modules/ sont temporaires dans le conteneur, vendor/ est le
# volume ploopi-tests-vendor), le docker-compose.yml de production n'est pas utilisé.
# Résultats dans tests/resultats/<version>/ (junit.xml, rapport.html, journaux,
# captures), comme avec tests/instance.sh test.
#
# Images : ploopi-tests:php<version>[-navigateur], construites au premier passage
# (tests/docker/Dockerfile), reconstruites seulement si le Dockerfile change.
#
# Réglages, par variable d'environnement :
#   PLOOPI_TEST_CA_BUNDLE   autorité de certification supplémentaire à approuver pendant la
#                           construction (réseau qui intercepte le HTTPS)
#   PLOOPI_NAV_PASSES       passes du parcours navigateur (défaut clair,sombre,telephone)
#   PLOOPI_BADGE_GIST       avec --publier : identifiant du gist du badge du README, mis à
#                           jour quand le commit testé est le dernier de origin/master ;
#                           défaut : celui de faf2019/ploopi, vide pour ne pas publier
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

set -u

cd "$(dirname "$0")/.." || exit 2
RACINE="$(pwd)"

# Dépôt d'un autre utilisateur (script lancé en root, par exemple) : git refuserait d'y
# travailler (« dubious ownership ») ; ce dépôt-ci est déclaré sûr, sans toucher à la
# configuration de git
git() { command git -c safe.directory="$RACINE" "$@"; }

VERSIONS=(8.1 8.2 8.3 8.4 8.5)
BASE_MARIADB="mariadb:10.11"

rouge() { printf '\033[31m%s\033[0m\n' "$*" >&2; }
vert()  { printf '\033[32m%s\033[0m\n' "$*"; }
info()  { printf '%s\n' "$*"; }
usage() { sed -n '2,32p' "$0" | sed 's/^# \{0,1\}//'; exit 2; }

CIBLE=""
NAVIGATEUR=0
PUBLIER=0
for a in "$@"; do
    case "$a" in
        --navigateur) NAVIGATEUR=1 ;;
        --publier) PUBLIER=1 ;;
        tout|8.1|8.2|8.3|8.4|8.5) CIBLE="$a" ;;
        *) usage ;;
    esac
done
[ -n "$CIBLE" ] || usage

# Question oui/non, posée seulement dans un terminal (non par défaut)
confirmer() { [ -t 0 ] || return 1; local r; read -r -p "$1 [o/N] " r; [[ "$r" =~ ^[oOyY] ]]; }

# docker info borné dans le temps quand c'est possible : un démon bloqué ne répond jamais
docker_repond()
{
    if command -v timeout >/dev/null; then timeout 20 docker info 2>&1 >/dev/null; else docker info 2>&1 >/dev/null; fi
}

# Démon Docker : cause exacte d'un échec, et (re)démarrage proposé quand il est arrêté ou bloqué
verifier_docker()
{
    local erreur relance etat
    erreur="$(docker_repond)" && return 0
    rouge "Le démon Docker ne répond pas :"
    printf '%s\n' "${erreur:-(délai de 20 s dépassé)}" | grep -v '^$' | tail -3 | sed 's/^/  /' >&2

    # Démon en route mais socket inaccessible : le relancer n'y changerait rien
    if printf '%s' "$erreur" | grep -qi 'permission denied'; then
        rouge "Accès refusé au démon : ajouter l'utilisateur au groupe docker (sudo usermod -aG docker \"\$USER\", puis rouvrir la session), ou lancer le script avec sudo."
        exit 2
    fi

    if [ "$(uname -s)" = Darwin ]; then
        relance="osascript -e 'quit app \"Docker\"' 2>/dev/null; sleep 3; open -a Docker"
        pgrep -f 'Docker Desktop' >/dev/null && etat="Docker Desktop est lancé mais ne répond pas." || etat="Docker Desktop n'est pas lancé."
    else
        # systemd réellement en service (un conteneur peut avoir systemctl sans systemd)
        if [ -d /run/systemd/system ] && systemctl list-unit-files docker.service >/dev/null 2>&1; then
            relance="sudo systemctl restart docker"
        else
            relance="sudo pkill -x dockerd; sleep 2; sudo sh -c 'dockerd > /var/log/dockerd.log 2>&1 &'"
        fi
        local pid
        pid="$(pgrep -x dockerd | head -1)"
        [ -n "$pid" ] && etat="Un démon dockerd tourne (pid ${pid}) mais ne répond pas." || etat="Aucun démon dockerd en route."
    fi
    info "$etat" >&2

    if ! confirmer "Le (re)démarrer maintenant (${relance}) ?"; then
        info "Pour le (re)démarrer : ${relance}" >&2
        exit 2
    fi
    eval "$relance" || { rouge "Échec de : ${relance}"; exit 2; }
    local i
    for i in $(seq 1 60); do
        docker_repond >/dev/null && { vert "Démon Docker prêt."; return 0; }
        sleep 2
    done
    rouge "Le démon ne répond toujours pas après 2 minutes."
    exit 2
}

command -v docker >/dev/null || { rouge "docker introuvable."; exit 2; }
verifier_docker

# Conteneurs et réseaux d'un passage interrompu (fenêtre fermée, kill -9…)
RESTES="$(docker ps -a --filter name=ploopi-tests- --format '{{.Names}}')"
if [ -n "$RESTES" ]; then
    info "Conteneurs restés d'un passage précédent :" >&2
    printf '  %s\n' $RESTES >&2
    if confirmer "Les arrêter et les supprimer ?"; then
        docker rm -f $RESTES >/dev/null
        for reseau in $(docker network ls -q --filter name=ploopi-tests-); do docker network rm "$reseau" >/dev/null 2>&1; done
    fi
fi

if [ -f tests/.instance ]; then
    rouge "Une instance de test est montée sur l'hôte (tests/.instance) : la démonter d'abord (tests/instance.sh down)."
    exit 2
fi

# --publier : le statut doit attester exactement le commit publié. Vérifié avant les tests
# (pour ne pas les lancer en vain), puis à nouveau avant de publier.
SHA=""
DEPOT=""
JETON="${GITHUB_TOKEN:-${GH_TOKEN:-}}"
publication_possible()
{
    if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
        # Les fichiers non suivis ne comptent pas : les suites n'examinent que le dépôt
        rouge "--publier : modifications non commitées ; committer (et pousser) avant de publier."
        return 1
    fi
    if [ "$(git rev-parse HEAD)" != "$SHA" ]; then
        rouge "--publier : le commit a changé pendant le passage ; rien n'est publié."
        return 1
    fi
    return 0
}
if [ $PUBLIER -eq 1 ]; then
    SHA="$(git rev-parse HEAD 2>&1)" || { rouge "--publier : git ne lit pas le dépôt : ${SHA}"; exit 2; }
    publication_possible || exit 2
    if [ -z "$(git branch -r --contains "$SHA" 2>/dev/null)" ]; then
        rouge "--publier : le commit ${SHA:0:7} n'est pas poussé ; le pousser d'abord (git push)."
        exit 2
    fi
    DEPOT="$(git remote get-url origin 2>/dev/null | sed -nE 's#^.*github\.com[:/]([^/]+/[^/]+)$#\1#p' | sed 's/\.git$//')"
    [ -n "$DEPOT" ] || { rouge "--publier : le dépôt origin n'est pas sur github.com."; exit 2; }
    # Gist du badge du README (tests/README.md) : celui du dépôt d'origin, pas celui d'un fork
    case "$DEPOT" in faf2019/ploopi|faf2019/ploopidev) PLOOPI_BADGE_GIST="${PLOOPI_BADGE_GIST-8992326b9a700fd3832d1f3ff85bb8cf}" ;; esac
    if ! { [ -n "$JETON" ] || { command -v gh >/dev/null && gh auth status >/dev/null 2>&1; }; }; then
        rouge "--publier : aucun accès à GitHub (gh auth login, ou GITHUB_TOKEN avec le droit « commit statuses »)."
        exit 2
    fi
    info "Résultats publiés sur ${DEPOT}@${SHA:0:7} (statuts tests-docker/php<version>)."
fi

# Publie le résultat d'une version : décompte de junit.xml, ou échec avant la suite
publier()
{
    local version="$1" code="$2" junit etat description
    junit="${RACINE}/tests/resultats/${version}/junit.xml"
    if [ -f "$junit" ]; then
        local n f e
        n="$(sed -n 's/.*<testsuites[^>]* tests="\([0-9]*\)".*/\1/p' "$junit" | head -1)"
        f="$(sed -n 's/.*<testsuites[^>]* failures="\([0-9]*\)".*/\1/p' "$junit" | head -1)"
        e="$(sed -n 's/.*<testsuites[^>]* errors="\([0-9]*\)".*/\1/p' "$junit" | head -1)"
        description="${n:-?} contrôles, ${f:-?} échec(s), ${e:-?} suite(s) interrompue(s)"
        [ $NAVIGATEUR -eq 1 ] && description="${description}, navigateur compris"
        if [ "$code" -eq 0 ]; then etat="success"; else etat="failure"; fi
    else
        etat="error"
        description="échec avant la suite (environnement)"
    fi
    description="${description} — tests/docker.sh"
    if [ -n "$JETON" ]; then
        curl -fsS -o /dev/null -X POST -H "Authorization: Bearer ${JETON}" -H "Accept: application/vnd.github+json" -H "Content-Type: application/json" \
            "https://api.github.com/repos/${DEPOT}/statuses/${SHA}" \
            -d "{\"state\":\"${etat}\",\"context\":\"tests-docker/php${version}\",\"description\":\"${description}\"}"
    else
        gh api -X POST "repos/${DEPOT}/statuses/${SHA}" -f "state=${etat}" -f "context=tests-docker/php${version}" \
            -f "description=${description}" >/dev/null
    fi && info "Statut publié : tests-docker/php${version} = ${etat} (${description})" \
       || rouge "Publication du statut tests-docker/php${version} impossible."
}

# Badge du README (PLOOPI_BADGE_GIST) : fichier du gist (ploopi-tests.json par défaut) au
# format « endpoint » de shields.io, mis à jour seulement pour le dernier commit de master
publier_badge()
{
    local message="$1" couleur="$2" fichier="${3:-ploopi-tests.json}" label="${4:-tests}" corps
    [ -n "${PLOOPI_BADGE_GIST:-}" ] || return 0
    if [ "$SHA" != "$(git rev-parse origin/master 2>/dev/null)" ]; then
        info "Badge inchangé : ${SHA:0:7} n'est pas le dernier commit de origin/master."
        return 0
    fi
    corps="{\"files\":{\"${fichier}\":{\"content\":\"{\\\"schemaVersion\\\":1,\\\"label\\\":\\\"${label}\\\",\\\"message\\\":\\\"${message}\\\",\\\"color\\\":\\\"${couleur}\\\"}\"}}}"
    if [ -n "$JETON" ]; then
        curl -fsS -o /dev/null -X PATCH -H "Authorization: Bearer ${JETON}" -H "Accept: application/vnd.github+json" -H "Content-Type: application/json" \
            "https://api.github.com/gists/${PLOOPI_BADGE_GIST}" -d "$corps"
    else
        printf '%s' "$corps" | gh api -X PATCH "gists/${PLOOPI_BADGE_GIST}" --input - >/dev/null
    fi && info "Badge publié : ${label} = ${message}" \
       || rouge "Publication du badge impossible (gist ${PLOOPI_BADGE_GIST} : droit « gist » du jeton ou de gh ?)."
}

# Badge des dépendances : résumé de composer audit écrit par tests/docker/entree.sh
badge_dependances()
{
    local resume
    resume="$(cat "${RACINE}/tests/resultats/$1/dependances.txt" 2>/dev/null)"
    [ -n "$resume" ] && publier_badge "${resume#* }" "${resume%% *}" ploopi-dependances.json "dépendances"
}

# Décompte d'une version pour le badge : « 1996 OK · php8.5 » ou « 3 échec(s) · php8.5 »
badge_version()
{
    local version="$1" code="$2" junit="${RACINE}/tests/resultats/$1/junit.xml" n f
    n="$(sed -n 's/.*<testsuites[^>]* tests="\([0-9]*\)".*/\1/p' "$junit" 2>/dev/null | head -1)"
    f="$(sed -n 's/.*<testsuites[^>]* failures="\([0-9]*\)".*/\1/p' "$junit" 2>/dev/null | head -1)"
    if [ "$code" -eq 0 ]; then
        publier_badge "${n:-?} OK · php${version}" brightgreen
    elif [ -f "$junit" ]; then
        publier_badge "${f:-?} échec(s) · php${version}" red
    else
        publier_badge "erreur · php${version}" lightgrey
    fi
}

# Construction : proxy HTTPS de l'hôte et autorité de certification transmis s'ils existent
ARGS_CONSTRUCTION=()
PROXY="${HTTPS_PROXY:-${https_proxy:-}}"
if [ -n "$PROXY" ]; then
    # Proxy local de l'hôte : joignable depuis la construction par le réseau de l'hôte
    ARGS_CONSTRUCTION+=(--network host --build-arg "https_proxy=${PROXY}" --build-arg "HTTPS_PROXY=${PROXY}")
fi
if [ -n "${PLOOPI_TEST_CA_BUNDLE:-}" ]; then
    [ -s "$PLOOPI_TEST_CA_BUNDLE" ] || { rouge "PLOOPI_TEST_CA_BUNDLE illisible : ${PLOOPI_TEST_CA_BUNDLE}"; exit 2; }
    ARGS_CONSTRUCTION+=(--secret "id=ca,src=${PLOOPI_TEST_CA_BUNDLE}")
fi

image()
{
    local version="$1" etage="base" image="ploopi-tests:php$1"
    [ $NAVIGATEUR -eq 1 ] && { etage="navigateur"; image="${image}-navigateur"; }
    info "Image ${image}…" >&2
    DOCKER_BUILDKIT=1 docker build -q "${ARGS_CONSTRUCTION[@]}" --target "$etage" \
        --build-arg "PHP_VERSION=${version}" -t "$image" "${RACINE}/tests/docker" >/dev/null \
        || { rouge "Construction de ${image} impossible."; return 2; }
    echo "$image"
}

# Base, conteneur PHP et réseau du passage en cours : retirés en fin de passage, et
# à la sortie du script quoi qu'il arrive (Ctrl-C compris)
NOM=""
nettoyer()
{
    [ -n "$NOM" ] || return 0
    docker rm -f "${NOM}-php" "${NOM}-db" >/dev/null 2>&1
    docker network rm "$NOM" >/dev/null 2>&1
    NOM=""
}
trap nettoyer EXIT
trap 'exit 130' INT TERM

# Un passage : base jetable sur un réseau dédié, conteneur PHP, puis nettoyage
passage()
{
    local version="$1" image code
    image="$(image "$version")" || return 2
    NOM="ploopi-tests-${version/./}-$$"
    local nom="$NOM"

    docker network create "$nom" >/dev/null || return 2

    info "MariaDB 10.11…"
    docker run -d --name "${nom}-db" --network "$nom" --network-alias db \
        -e MARIADB_ROOT_PASSWORD=root --tmpfs /var/lib/mysql "$BASE_MARIADB" >/dev/null || return 2
    local i
    for i in $(seq 1 60); do
        docker exec "${nom}-db" healthcheck.sh --connect --innodb_initialized >/dev/null 2>&1 && break
        [ "$i" -eq 60 ] && { rouge "MariaDB n'a pas démarré."; return 2; }
        sleep 1
    done

    local -a options=()
    [ $NAVIGATEUR -eq 1 ] && options+=(--navigateur)
    mkdir -p "${RACINE}/data"
    # Résultats d'un passage précédent retirés : seul un rapport de ce passage est annoncé
    rm -rf "${RACINE}/tests/resultats/${version}"
    docker run --rm --name "${nom}-php" --network "$nom" \
        -v "${RACINE}:/ploopi" -v "${RACINE}/config:/ploopi-config:ro" \
        -v "${RACINE}/modules:/ploopi-modules:ro" \
        --tmpfs /ploopi/config --tmpfs /ploopi/data --tmpfs /ploopi/modules \
        -v ploopi-tests-composer:/composer-cache -v ploopi-tests-vendor:/ploopi/vendor \
        -e PLOOPI_TEST_DB_HOST=db -e PLOOPI_TEST_DB_ADMIN=root -e PLOOPI_TEST_DB_ADMINPWD=root \
        -e "PLOOPI_TEST_DB_USER_HOSTS=%" -e "PLOOPI_TESTS_BASE=MariaDB 10.11" \
        -e "HOTE_UID=$(id -u)" -e "HOTE_GID=$(id -g)" \
        ${PLOOPI_NAV_PASSES:+-e "PLOOPI_NAV_PASSES=${PLOOPI_NAV_PASSES}"} \
        "$image" bash tests/docker/entree.sh ${options[@]+"${options[@]}"}
    code=$?
    nettoyer
    if [ -f "${RACINE}/tests/resultats/${version}/rapport.html" ]; then
        info "Résultats : tests/resultats/${version}/rapport.html"
    else
        rouge "Aucun résultat : échec avant la suite (voir ci-dessus)."
    fi
    return $code
}

if [ "$CIBLE" != "tout" ]; then
    passage "$CIBLE"
    c=$?
    [ $PUBLIER -eq 1 ] && publication_possible && { publier "$CIBLE" "$c"; badge_version "$CIBLE" "$c"; badge_dependances "$CIBLE"; }
    exit $c
fi

# Les cinq versions, l'une après l'autre ; bilan à la fin
declare -A BILAN=()
code_global=0
for v in "${VERSIONS[@]}"; do
    info ""
    info "════════ PHP ${v} ════════"
    passage "$v"
    c=$?
    BILAN[$v]=$c
    [ $c -ne 0 ] && code_global=1
done

info ""
info "════════ Bilan ════════"
for v in "${VERSIONS[@]}"; do
    if [ "${BILAN[$v]}" -eq 0 ]; then vert "  PHP ${v} : vert"; else rouge "  PHP ${v} : rouge (code ${BILAN[$v]}) — tests/resultats/${v}/rapport.html"; fi
done
if [ $PUBLIER -eq 1 ] && publication_possible; then
    info ""
    rouges=()
    for v in "${VERSIONS[@]}"; do
        publier "$v" "${BILAN[$v]}"
        [ "${BILAN[$v]}" -eq 0 ] || rouges+=("$v")
    done
    if [ ${#rouges[@]} -eq 0 ]; then
        publier_badge "OK · php${VERSIONS[0]} à ${VERSIONS[-1]}" brightgreen
    else
        publier_badge "échec · php${rouges[*]}" red
    fi
    badge_dependances "${VERSIONS[-1]}"
fi
exit $code_global
