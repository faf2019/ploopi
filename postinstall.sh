#!/bin/sh

# Fixe les droits après installation ou mise à jour de Ploopi
# On suppose que l'utilisateur d'Apache est www-data du groupe www-data

chown -R www-data:www-data .
find . -type d -print0 | xargs -0 -n 1 chmod 500
find . -type f -print0 | xargs -0 -n 1 chmod 400
find {data,config,modules} -type d -print0 | xargs -0 -n 1 chmod 700
find {data,config,modules} -type f -print0 | xargs -0 -n 1 chmod 600
find bin -type f -print0 | xargs -0 -n 1 chmod 500
chmod 500 ./cgi/upload.cgi
