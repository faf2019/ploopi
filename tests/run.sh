#!/bin/bash
#
# Suite de vérification des correctifs de sécurité de Ploopi.
#
# Usage : tests/run.sh [suite...]
#   tests/run.sh                 exécute toutes les suites
#   tests/run.sh 01_http         exécute une suite précise
#
# Les suites unitaires ne demandent que PHP en ligne de commande. Les suites qui
# éprouvent une instance réelle réclament tests/config.php (copier
# tests/config.php.model) et une instance en fonctionnement servant ce dépôt ;
# à défaut elles se déclarent non évaluées. Voir tests/README.md.
#
# Résultats dans tests/resultats/<version de PHP>/ (non versionné, remplacé à chaque
# passage ; autre dossier : PLOOPI_TESTS_RESULTATS) : junit.xml, rapport.html, et la
# sortie de chaque suite (tests/rapport.php).

cd "$(dirname "$0")/.." || exit 2

# tests/config.php est facultatif : il décrit l'instance à éprouver. Sans lui, les
# suites unitaires s'exécutent quand même et les suites qui réclament une instance
# ou une base se déclarent non évaluées.
if [ ! -f tests/config.php ]; then
    echo "tests/config.php est absent : seules les suites unitaires seront évaluées." >&2
    echo "Pour éprouver une instance, copier tests/config.php.model et l'adapter." >&2
    echo
fi

suites=("$@")
if [ ${#suites[@]} -eq 0 ]; then
    # Découverte automatique : toute suite tests/NN_nom.php est exécutée, dans l'ordre
    suites=()
    for f in tests/[0-9][0-9]_*.php; do
        [ -e "$f" ] || continue
        suites+=("$(basename "$f" .php)")
    done
fi

version_php="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
resultats="${PLOOPI_TESTS_RESULTATS:-tests/resultats/${version_php}}"
rm -rf "$resultats"
mkdir -p "${resultats}/sorties"
export PLOOPI_TESTS_CONTROLES="$(cd "$resultats" && pwd)/controles.jsonl"
: > "$PLOOPI_TESTS_CONTROLES"
printf '{"php":"%s","php_complet":"%s","commit":"%s","base":"%s"}\n' "$version_php" "$(php -r 'echo PHP_VERSION;')" \
    "$(git -c safe.directory="$(pwd)" rev-parse --short HEAD 2>/dev/null)" "${PLOOPI_TESTS_BASE:-}" > "${resultats}/passage.json"

total_ok=0
total_ko=0
interrompues=()
total_skip=0
echec=0

for suite in "${suites[@]}"; do
    if [ ! -f "tests/${suite}.php" ]; then
        echo "Suite inconnue : ${suite}" >&2
        exit 2
    fi

    debut=$(date +%s.%N)
    sortie=$(php "tests/${suite}.php" 2>&1)
    rc=$?
    duree=$(awk -v d="$debut" -v f="$(date +%s.%N)" 'BEGIN { printf "%.3f", f - d }')
    echo "$sortie"
    printf '%s\n' "$sortie" | sed 's/\x1b\[[0-9;]*m//g' > "${resultats}/sorties/${suite}.txt"

    ok=$(echo "$sortie"   | grep -oP '═══ \K[0-9]+(?= OK)')
    ko=$(echo "$sortie"   | grep -oP 'OK / \K[0-9]+(?= KO)')
    skip=$(echo "$sortie" | grep -oP 'KO / \K[0-9]+(?= non)')

    total_ok=$((total_ok + ${ok:-0}))
    total_ko=$((total_ko + ${ko:-0}))
    total_skip=$((total_skip + ${skip:-0}))

    # Pas de bilan : la suite s'est interrompue (erreur fatale…), ses contrôles
    # restants n'ont pas tourné. Comptée comme un échec, et nommée dans le total.
    interrompue=false
    if [ -z "$ok" ]; then
        total_ko=$((total_ko + 1))
        interrompues+=("$suite")
        interrompue=true
        echo "Suite ${suite} interrompue avant son bilan (code ${rc})." >&2
    fi
    printf '{"suite":"%s","rc":%d,"duree":%s,"interrompue":%s}\n' "$suite" "$rc" "$duree" "$interrompue" >> "${resultats}/suites.jsonl"

    [ "$rc" -ne 0 ] && echec=1
done

echo
echo "════════════════════════════════════════════"
if [ "$total_skip" -gt 0 ]; then
    printf "  TOTAL : %d OK / %d KO / %d non évalué(s)\n" "$total_ok" "$total_ko" "$total_skip"
else
    printf "  TOTAL : %d OK / %d KO\n" "$total_ok" "$total_ko"
fi
[ ${#interrompues[@]} -gt 0 ] && echo "  Interrompue(s) : ${interrompues[*]}"
echo "════════════════════════════════════════════"

php tests/rapport.php "$resultats"

exit $echec
