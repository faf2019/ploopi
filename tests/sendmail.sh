#!/bin/sh
# Faux sendmail de l'instance de test (sendmail_path, voir instance.sh) : ajoute le
# courriel reçu sur l'entrée standard au fichier passé en argument, précédé d'un
# séparateur. Les suites le lisent par courriels() (bootstrap.php).
{ echo '@@COURRIEL@@'; cat; } >> "$1"
