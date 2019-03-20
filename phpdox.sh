#!/bin/sh
# Génération de la documentation du framework
php vendor/phploc/phploc/phploc include --log-xml phploc.xml
php vendor/theseer/phpdox

