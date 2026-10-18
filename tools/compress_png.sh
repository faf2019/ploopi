#!/bin/sh

# Optimisation sans perte des images PNG (nécessite optipng)
#
# Copyright (c) 2008-2026 Ovensia et contributeurs
# GNU General Public License (GPL) version 2 ou ultérieure

cd "$(dirname "$0")/.."

for i in $(find ./img ./install ./templates ./modules/system -name '*.png' -type f)
do
    ta=$(stat -c "%s" "$i")
    optipng -q -o7 "$i"
    tb=$(stat -c "%s" "$i")
    echo "$i : $ta => $tb"
done
