#!/bin/bash
#
# Lance les suites de tests Ploopi.
#
# Usage :
#   tests/run.sh              lance toutes les suites
#   tests/run.sh 02 05        lance les suites dont le nom commence par 02 ou 05
#
# Les suites sont découvertes automatiquement : tout fichier tests/NN_nom.php est
# exécuté, dans l'ordre de son numéro. Chaque suite est un processus PHP autonome
# et retourne 0 si tous ses contrôles passent.

cd "$(dirname "$0")/.." || exit 1

suites=()
for f in tests/[0-9][0-9]_*.php; do
    [ -e "$f" ] || continue
    nom="$(basename "$f" .php)"
    if [ $# -gt 0 ]; then
        garde=0
        for filtre in "$@"; do
            case "$nom" in "$filtre"*) garde=1;; esac
        done
        [ $garde -eq 1 ] || continue
    fi
    suites+=("$nom")
done

if [ ${#suites[@]} -eq 0 ]; then
    echo "Aucune suite à exécuter."
    exit 1
fi

echec=0
resume=""

for suite in "${suites[@]}"; do
    echo ""
    echo "=============================================================="
    echo "== $suite"
    echo "=============================================================="
    sortie="$(php "tests/${suite}.php" 2>&1)"
    code=$?
    echo "$sortie"
    bilan="$(echo "$sortie" | grep -E '^[a-z0-9_]+ : [0-9]+ OK' | tail -1)"
    [ -n "$bilan" ] || bilan="$suite : pas de bilan (sortie anormale)"
    if [ $code -ne 0 ]; then
        echec=1
        resume="${resume}  ECHEC  ${bilan}\n"
    else
        resume="${resume}  ok     ${bilan}\n"
    fi
done

echo ""
echo "=============================================================="
echo "== Bilan"
echo "=============================================================="
printf "%b" "$resume"

exit $echec
