#!/bin/bash
#
# Monte une instance Ploopi jetable et lance la suite de tests dessus.
#
# Les suites 01, 04 et 06 éprouvent des parcours HTTP réels : elles réclament une
# instance installée et servie. Ce script la fabrique de bout en bout — base dédiée,
# installation par l'assistant, serveur PHP intégré, tests/config.php — puis la
# détruit. Les suites unitaires, elles, n'ont besoin de rien de tout cela.
#
# Usage :
#   tests/instance.sh up       monte l'instance et écrit tests/config.php
#   tests/instance.sh test     up, puis tests/run.sh, puis down  (usage courant)
#   tests/instance.sh run      lance la suite sur une instance déjà montée
#   tests/instance.sh down     arrête le serveur et supprime bases et configuration
#   tests/instance.sh status   état courant
#   tests/instance.sh navigateur
#                              parcours navigateur (Playwright) sur l'instance montée :
#                              installe d'abord tous les modules (tests/navigateur/)
#   tests/instance.sh test --navigateur
#                              test, puis le parcours navigateur avant de démonter
#
# Résultats dans tests/resultats/<version de PHP>/ (non versionné) : junit.xml et
# rapport.html (tests/rapport.php), captures du parcours navigateur (navigateur/), et
# journaux de l'instance conservés au démontage (journaux/).
#
# Réglages, par variable d'environnement :
#   PLOOPI_TEST_PORT        port du serveur       (défaut 8099)
#   PLOOPI_TEST_DB          base de travail       (défaut ploopi_instance_test)
#   PLOOPI_TEST_DB_ADMIN    compte MySQL admin    (défaut root)
#   PLOOPI_TEST_DB_ADMINPWD mot de passe de ce compte (défaut vide)
#   PLOOPI_TEST_DB_HOST     serveur MySQL vu par Ploopi (défaut 127.0.0.1)
#   PLOOPI_TEST_DB_USER_HOSTS hôtes autorisés pour le compte de test, séparés par des
#                           espaces (défaut « localhost 127.0.0.1 »). Un serveur MySQL en
#                           conteneur voit arriver les connexions depuis son réseau, pas
#                           depuis 127.0.0.1 : y ajouter « % » (cas de l'intégration continue)
#   PLOOPI_TEST_S3_ENDPOINT URL d'un serveur S3 (ex. http://127.0.0.1:9000) : documents de la
#                           GED rangés dans un dépôt S3 (suite 42), au lieu du dossier local
#   PLOOPI_TEST_S3_REGION / _BUCKET / _KEY / _SECRET
#                           région, dépôt (créé s'il manque), identifiants (défauts :
#                           us-east-1, ploopi-tests, ploopi, ploopi-secret)
#   PLOOPI_NAV_PASSES       passes du parcours navigateur (défaut clair,sombre,telephone)
#
# Prérequis : php (CLI), le client mysql, curl, et un serveur MySQL/MariaDB joignable
# sur lequel le compte admin peut créer une base.
#
# Ce script ne touche JAMAIS à une instance existante : il refuse de démarrer si
# config/config.php est présent et qu'il ne l'a pas écrit lui-même.

set -u

cd "$(dirname "$0")/.." || exit 2
RACINE="$(pwd)"

PORT="${PLOOPI_TEST_PORT:-8099}"
DB="${PLOOPI_TEST_DB:-ploopi_instance_test}"
DB_INSTALL="${DB}_neuve"
DB_HOST="${PLOOPI_TEST_DB_HOST:-127.0.0.1}"
DB_USER="ploopi_test"
DB_PASS="ploopi_test"
ADMIN="${PLOOPI_TEST_DB_ADMIN:-root}"
ADMINPWD="${PLOOPI_TEST_DB_ADMINPWD:-}"
DB_USER_HOSTS="${PLOOPI_TEST_DB_USER_HOSTS:-localhost 127.0.0.1}"
S3_ENDPOINT="${PLOOPI_TEST_S3_ENDPOINT:-}"
S3_REGION="${PLOOPI_TEST_S3_REGION:-us-east-1}"
S3_BUCKET="${PLOOPI_TEST_S3_BUCKET:-ploopi-tests}"
S3_KEY="${PLOOPI_TEST_S3_KEY:-ploopi}"
S3_SECRET="${PLOOPI_TEST_S3_SECRET:-ploopi-secret}"

ADMIN_LOGIN="admin"
ADMIN_PASSWORD="Adm1n!Test2026"

CONFIG="${RACINE}/config/config.php"
CONFIG_TESTS="${RACINE}/tests/config.php"
ETAT="${RACINE}/tests/.instance"
PREPARATION="${RACINE}/tests/.instance_navigateur.json"
RESULTATS="${PLOOPI_TESTS_RESULTATS:-${RACINE}/tests/resultats/$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null)}"
SESSIONS="${RACINE}/data/sessions_tests"
JOURNAL="${RACINE}/data/instance_tests.log"
COURRIELS="${RACINE}/data/courriels_tests.txt"
BASE_URL="http://127.0.0.1:${PORT}"

rouge()  { printf '\033[31m%s\033[0m\n' "$*" >&2; }
vert()   { printf '\033[32m%s\033[0m\n' "$*"; }
info()   { printf '%s\n' "$*"; }

# Le compte d'administration se connecte souvent par la socket locale (root@localhost)
# et pas en TCP : on retient la forme qui répond, une fois pour toutes.
ADMIN_HOTE=""
# Client MariaDB 11.4 et suivants (Debian 13…) : TLS exigé par défaut, refusé par un
# serveur sans TLS (« SSL is required, but the server does not support it »). On retente
# alors sans TLS (base locale ou de test).
ADMIN_SSL=""

mysql_admin()
{
    local args=()
    [ -n "$ADMIN_SSL" ] && args+=("$ADMIN_SSL")
    [ -n "$ADMIN_HOTE" ] && args+=(-h "$ADMIN_HOTE")
    args+=(-u "$ADMIN")
    [ -n "$ADMINPWD" ] && args+=(-p"$ADMINPWD")

    mysql "${args[@]}" "$@"
}

resoudre_admin()
{
    local candidat
    for ADMIN_SSL in "" "--skip-ssl"; do
        for candidat in "" "$DB_HOST" "localhost"; do
            ADMIN_HOTE="$candidat"
            if mysql_admin -e 'SELECT 1' >/dev/null 2>&1; then return 0; fi
        done
    done

    ADMIN_HOTE=""
    ADMIN_SSL=""
    return 1
}

prerequis()
{
    local manquant=0

    for binaire in php curl mysql; do
        command -v "$binaire" >/dev/null 2>&1 || { rouge "$binaire est introuvable."; manquant=1; }
    done

    [ $manquant -eq 0 ] || return 1

    if ! resoudre_admin; then
        rouge "Connexion MySQL impossible avec le compte '${ADMIN}' (socket locale, ${DB_HOST}, localhost)."
        rouge "Renseigner PLOOPI_TEST_DB_ADMIN et PLOOPI_TEST_DB_ADMINPWD."
        return 1
    fi

    if [ ! -f "${RACINE}/vendor/autoload.php" ]; then
        rouge "vendor/ est absent : lancer d'abord composer install."
        return 1
    fi

    return 0
}

serveur_actif()
{
    curl -s -o /dev/null --max-time 3 "${BASE_URL}/" 2>/dev/null
}

up()
{
    if [ -f "$ETAT" ]; then
        info "Une instance de test est déjà montée (voir ${ETAT#$RACINE/})."
        return 0
    fi

    if [ -f "$CONFIG" ]; then
        rouge "config/config.php existe déjà : ce dépôt sert une instance."
        rouge "Ce script refuse d'y toucher. La déplacer d'abord si elle n'est pas en service."
        return 2
    fi

    prerequis || return 2

    if serveur_actif; then
        rouge "Le port ${PORT} est déjà occupé. Choisir un autre port avec PLOOPI_TEST_PORT."
        return 2
    fi

    info "Base de données…"

    # Le compte de test, pour chacun des hôtes autorisés
    local hote comptes="" droits=""
    for hote in $DB_USER_HOSTS; do
        comptes+="CREATE USER IF NOT EXISTS '${DB_USER}'@'${hote}' IDENTIFIED BY '${DB_PASS}';"
        droits+="GRANT ALL ON \`${DB}\`.* TO '${DB_USER}'@'${hote}';"
        droits+="GRANT ALL ON \`${DB_INSTALL}\`.* TO '${DB_USER}'@'${hote}';"
        # La suite 04 déroule une installation neuve : le compte doit pouvoir créer la base.
        droits+="GRANT CREATE ON *.* TO '${DB_USER}'@'${hote}';"
    done

    mysql_admin -e "
        DROP DATABASE IF EXISTS \`${DB}\`;
        CREATE DATABASE \`${DB}\` DEFAULT CHARACTER SET utf8;
        ${comptes}
        ${droits}
        FLUSH PRIVILEGES;" || { rouge "Création de la base impossible."; return 2; }

    mkdir -p "$SESSIONS" "${RACINE}/data"

    info "Serveur PHP sur ${BASE_URL}…"
    # request_order vide et variables_order=EGPCS : la configuration d'un PHP sans
    # php.ini (images Docker officielles), où les cookies entrent dans $_REQUEST.
    # Le socle doit s'en passer (loader::boot() reconstruit $_REQUEST) : la suite
    # tourne dans le cas le plus défavorable.
    # sendmail_path : les courriels envoyés par mail() s'ajoutent à $COURRIELS, que
    # les suites lisent (courriels() dans bootstrap.php) au lieu de les expédier.
    php -S "127.0.0.1:${PORT}" -t "$RACINE" \
        -d session.save_path="$SESSIONS" \
        -d request_order= -d variables_order=EGPCS \
        -d short_open_tag=On \
        -d display_errors=Off \
        -d sendmail_path="sh ${RACINE}/tests/sendmail.sh ${COURRIELS}" \
        "${RACINE}/tests/routeur.php" > "$JOURNAL" 2>&1 &
    local pid=$!

    printf 'pid=%s\nport=%s\ndb=%s\ndb_install=%s\n' "$pid" "$PORT" "$DB" "$DB_INSTALL" > "$ETAT"

    local essai=0
    until serveur_actif; do
        essai=$((essai + 1))
        if [ $essai -gt 40 ] || ! kill -0 "$pid" 2>/dev/null; then
            rouge "Le serveur n'a pas démarré. Journal : ${JOURNAL#$RACINE/}"
            down
            return 2
        fi
        sleep 0.25
    done

    # Journal des erreurs de l'instance (_PLOOPI_LOG_ERRORS_FILE du modèle de configuration) :
    # la suite 99 n'en lit que la partie écrite par cette instance, installation comprise
    local journal_erreurs debut_erreurs
    journal_erreurs="$(sed -n "s/.*define *('_PLOOPI_LOG_ERRORS_FILE', *'\([^']*\)').*/\1/p" "${RACINE}/config/config.php.model" | head -1)"
    debut_erreurs=0
    [ -n "$journal_erreurs" ] && [ -f "$journal_erreurs" ] && debut_erreurs="$(wc -c < "$journal_erreurs" | tr -d ' ')"

    info "Installation par l'assistant…"

    # La clé secrète proposée par le formulaire est celle que l'assistant génère.
    local formulaire cle
    formulaire="$(curl -s "${BASE_URL}/")"
    cle="$(printf '%s' "$formulaire" | sed -n 's/.*name="SECRETKEY" value="\([^"]*\)".*/\1/p' | head -1)"

    if [ -z "$cle" ]; then
        rouge "L'assistant d'installation n'a pas été servi. Journal : ${JOURNAL#$RACINE/}"
        down
        return 2
    fi

    curl -s -o /dev/null \
        --data-urlencode "ADMIN_LOGIN=${ADMIN_LOGIN}" \
        --data-urlencode "ADMIN_PASSWORD=${ADMIN_PASSWORD}" \
        --data-urlencode "ADMIN_MAIL=admin@example.test" \
        --data-urlencode "DB_SERVER=${DB_HOST}" \
        --data-urlencode "DB_LOGIN=${DB_USER}" \
        --data-urlencode "DB_PASSWORD=${DB_PASS}" \
        --data-urlencode "DB_DATABASE=${DB}" \
        --data-urlencode "DATAPATH=${RACINE}/data" \
        --data-urlencode "SYS_MAIL=sys@example.test" \
        --data-urlencode "ELASTIC_HOST=" \
        --data-urlencode "CIPHER=AES-128-CBC" \
        --data-urlencode "HASH_ALGO=bcrypt" \
        --data-urlencode "SECRETKEY=${cle}" \
        "${BASE_URL}/"

    if [ ! -f "$CONFIG" ]; then
        rouge "L'assistant n'a pas écrit config/config.php. Journal : ${JOURNAL#$RACINE/}"
        down
        return 2
    fi

    echo "config=1" >> "$ETAT"

    # Documents rangés dans un dépôt S3 (suite 42), sur demande : le serveur S3 n'est
    # pas fourni (MinIO, moto_server…) ; le dépôt est créé par la suite s'il manque
    if [ -n "$S3_ENDPOINT" ]; then
        sed -i \
            -e "s|define ('_PLOOPI_S3_ACTIVATED', false);|define ('_PLOOPI_S3_ACTIVATED', true);|" \
            -e "s|define ('_PLOOPI_S3_ENDPOINT_URL', '');|define ('_PLOOPI_S3_ENDPOINT_URL', '${S3_ENDPOINT}');|" \
            -e "s|define ('_PLOOPI_S3_REGION', '');|define ('_PLOOPI_S3_REGION', '${S3_REGION}');|" \
            -e "s|define ('_PLOOPI_S3_BUCKET', '');|define ('_PLOOPI_S3_BUCKET', '${S3_BUCKET}');|" \
            -e "s|define ('_PLOOPI_S3_AUTH_KEY', '');|define ('_PLOOPI_S3_AUTH_KEY', '${S3_KEY}');|" \
            -e "s|define ('_PLOOPI_S3_AUTH_SECRET', '');|define ('_PLOOPI_S3_AUTH_SECRET', '${S3_SECRET}');|" \
            "$CONFIG"
        if ! grep -q "define ('_PLOOPI_S3_ACTIVATED', true);" "$CONFIG"; then
            rouge "S3 demandé (PLOOPI_TEST_S3_ENDPOINT) mais config/config.php n'a pas pu être réglé."
            down
            return 2
        fi
        info "Documents rangés dans le dépôt S3 ${S3_BUCKET} de ${S3_ENDPOINT}."
    fi

    cat > "$CONFIG_TESTS" <<EOF
<?php
// Écrit par tests/instance.sh — instance jetable. Ne pas versionner.
return array(
    'base_url'          => '${BASE_URL}',
    'db_host'           => '${DB_HOST}',
    'db_name'           => '${DB}',
    'db_user'           => '${DB_USER}',
    'db_pass'           => '${DB_PASS}',
    'admin_login'       => '${ADMIN_LOGIN}',
    'admin_password'    => '${ADMIN_PASSWORD}',
    'session_path'      => '${SESSIONS}',
    'courriels'         => '${COURRIELS}',
    'allow_destructive' => true,
    'install_db'        => '${DB_INSTALL}',
    'journal_erreurs'   => '${journal_erreurs}',
    'journal_debut'     => ${debut_erreurs},
);
EOF
    echo "config_tests=1" >> "$ETAT"

    vert "Instance prête sur ${BASE_URL} (admin / ${ADMIN_PASSWORD})."
    return 0
}

# Journaux de l'instance gardés dans les résultats : serveur PHP, part du journal
# d'erreurs de Ploopi écrite par cette instance, courriels envoyés
conserver_journaux()
{
    [ -f "$CONFIG_TESTS" ] || return 0
    local journal_erreurs debut
    journal_erreurs="$(sed -n "s/.*'journal_erreurs' *=> *'\([^']*\)'.*/\1/p" "$CONFIG_TESTS")"
    debut="$(sed -n "s/.*'journal_debut' *=> *\([0-9]*\).*/\1/p" "$CONFIG_TESTS")"
    mkdir -p "${RESULTATS}/journaux"
    [ -f "$JOURNAL" ] && cp "$JOURNAL" "${RESULTATS}/journaux/serveur.log"
    [ -f "$COURRIELS" ] && cp "$COURRIELS" "${RESULTATS}/journaux/courriels.txt"
    if [ -n "$journal_erreurs" ] && [ -f "$journal_erreurs" ]; then
        tail -c +"$(( ${debut:-0} + 1 ))" "$journal_erreurs" > "${RESULTATS}/journaux/ploopi_erreurs.log"
    fi
    php "${RACINE}/tests/rapport.php" "$RESULTATS" >/dev/null
}

down()
{
    if [ -f "$ETAT" ]; then
        conserver_journaux
        local pid
        pid="$(sed -n 's/^pid=//p' "$ETAT")"
        [ -n "$pid" ] && kill "$pid" 2>/dev/null
        grep -q '^config=1' "$ETAT" 2>/dev/null && rm -f "$CONFIG"
        grep -q '^config_tests=1' "$ETAT" 2>/dev/null && rm -f "$CONFIG_TESTS"
        # Modules déployés par la préparation du parcours navigateur (copies de install/)
        sed -n 's/^module=//p' "$ETAT" | while read -r dossier; do
            case "$dossier" in "${RACINE}/modules/"?*) rm -rf "$dossier" ;; esac
        done
        rm -f "$PREPARATION"
        rm -f "$ETAT"
    fi

    resoudre_admin && mysql_admin -e "DROP DATABASE IF EXISTS \`${DB}\`; DROP DATABASE IF EXISTS \`${DB_INSTALL}\`;" >/dev/null 2>&1
    rm -rf "$SESSIONS"
    rm -f "$JOURNAL" "$COURRIELS"

    vert "Instance démontée."
    return 0
}

status()
{
    if [ ! -f "$ETAT" ]; then info "Aucune instance de test montée."; return 1; fi

    info "État : ${ETAT#$RACINE/}"
    sed 's/^/  /' "$ETAT"
    if serveur_actif; then vert "Serveur joignable sur ${BASE_URL}."; else rouge "Serveur injoignable sur ${BASE_URL}."; fi
    return 0
}

# Parcours navigateur : prépare l'instance une fois (tous les modules installés et
# instanciés, ce qui la rend non vierge : à lancer après la suite), puis tests/navigateur/parcours.js
navigateur()
{
    [ -f "$ETAT" ] || { rouge "Aucune instance montée : lancer d'abord tests/instance.sh up."; return 2; }
    command -v node >/dev/null || { rouge "node introuvable : le parcours navigateur exige Node.js et Playwright."; return 2; }
    export NODE_PATH="${NODE_PATH:-$(npm root -g 2>/dev/null)}"
    node -e "require(process.env.PLAYWRIGHT_MODULE || 'playwright')" 2>/dev/null \
        || { rouge "Module Node « playwright » introuvable (npm install -g playwright && npx playwright install chromium)."; return 2; }

    if [ ! -f "$PREPARATION" ]; then
        info "Préparation : installation et instanciation de tous les modules…"
        local avant dossier
        avant="$(ls -d "${RACINE}"/modules/*/ 2>/dev/null)"
        php "${RACINE}/tests/navigateur/preparer.php" > "${PREPARATION}.tmp" || { rouge "Préparation impossible."; cat "${PREPARATION}.tmp" >&2; rm -f "${PREPARATION}.tmp"; return 2; }
        for dossier in "${RACINE}"/modules/*/; do
            grep -qxF "$dossier" <<< "$avant" || echo "module=${dossier%/}" >> "$ETAT"
        done
        mv "${PREPARATION}.tmp" "$PREPARATION"
        grep -q '"erreurs": \[\]' "$PREPARATION" || { rouge "Erreurs de préparation :"; cat "$PREPARATION" >&2; }
    fi

    local journal_erreurs
    journal_erreurs="$(sed -n "s/.*'journal_erreurs' *=> *'\([^']*\)'.*/\1/p" "$CONFIG_TESTS")"
    PLOOPI_NAV_URL="$BASE_URL" PLOOPI_NAV_LOGIN="$ADMIN_LOGIN" PLOOPI_NAV_MDP="$ADMIN_PASSWORD" \
    PLOOPI_NAV_JOURNAL="$journal_erreurs" PLOOPI_NAV_JOURNAL_SERVEUR="$JOURNAL" \
    PLOOPI_NAV_PREPARATION="$(cat "$PREPARATION")" PLOOPI_NAV_DOSSIER="${RESULTATS}/navigateur" \
        node "${RACINE}/tests/navigateur/parcours.js" "$@"
    local code=$?
    mkdir -p "$RESULTATS"
    php "${RACINE}/tests/rapport.php" "$RESULTATS"
    return $code
}

case "${1:-test}" in
    up)     up ;;
    down)   down ;;
    status) status ;;
    navigateur)
        shift
        navigateur "$@"
        ;;
    run)
        [ -f "$ETAT" ] || { rouge "Aucune instance montée : lancer d'abord tests/instance.sh up."; exit 2; }
        shift || true
        ./tests/run.sh "$@"
        ;;
    test)
        shift || true
        avec_navigateur=0
        args=()
        for a in "$@"; do
            if [ "$a" = "--navigateur" ]; then avec_navigateur=1; else args+=("$a"); fi
        done
        up || exit $?
        ./tests/run.sh ${args[@]+"${args[@]}"}
        code=$?
        if [ $avec_navigateur -eq 1 ]; then
            navigateur
            code_nav=$?
            [ $code -eq 0 ] && code=$code_nav
        fi
        down
        exit $code
        ;;
    *)
        sed -n '2,47p' "$0" | sed 's/^# \{0,1\}//'
        exit 2
        ;;
esac
