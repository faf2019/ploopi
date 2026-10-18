# Image de test de Ploopi : PHP CLI de la version demandée, avec les extensions de la CI
# (.github/workflows/tests.yml), Composer et le client MariaDB que demande
# tests/instance.sh. Le dépôt n'y est pas copié : tests/docker.sh le monte en volume.
#
# Deux étages, construits par tests/docker.sh (pas de « docker build » à la main) :
#   base        PHP + extensions + Composer + client MariaDB
#   navigateur  base + Node.js, Playwright et Chromium (tests/navigateur/)

ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-cli-bookworm AS base

# Autorité de certification supplémentaire, facultative (réseau qui intercepte le
# HTTPS : proxy d'entreprise, environnement cloud) : fournie par tests/docker.sh en
# secret de construction quand PLOOPI_TEST_CA_BUNDLE est défini, sans effet sinon.
# Derrière un proxy HTTPS (https_proxy, transmis par tests/docker.sh), apt passe lui
# aussi en HTTPS par ce proxy : un tel proxy ne relaie pas toujours le HTTP simple.
RUN --mount=type=secret,id=ca \
    if [ -s /run/secrets/ca ]; then \
        cp /run/secrets/ca /usr/local/share/ca-certificates/ploopi-tests-ca.crt \
        && update-ca-certificates >/dev/null; \
    fi \
    && if [ -n "${https_proxy:-}" ]; then \
        sed -i 's#http://deb.debian.org#https://deb.debian.org#g' /etc/apt/sources.list.d/debian.sources \
        && echo "Acquire::https::Proxy \"${https_proxy}\";" > /etc/apt/apt.conf.d/99proxy; \
    fi

# Extensions de la CI : mysqli, gd et zip à compiler ; curl, xml, mbstring et openssl
# sont déjà dans l'image officielle (les recompiler échoue). unzip et git servent à
# Composer, le client MariaDB et curl à tests/instance.sh.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype-dev libjpeg62-turbo-dev libpng-dev libzip-dev \
        mariadb-client curl unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli gd zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_CACHE_DIR=/composer-cache

WORKDIR /ploopi

# ————————————————— Parcours navigateur —————————————————
FROM base AS navigateur

ARG PLAYWRIGHT_VERSION=1.56.1

# Node.js de l'image officielle (même Debian, même glibc) ; Chromium et ses
# dépendances système par Playwright lui-même
COPY --from=node:22-bookworm-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-bookworm-slim /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
ENV NODE_PATH=/usr/local/lib/node_modules PLAYWRIGHT_BROWSERS_PATH=/opt/pw-browsers \
    NODE_EXTRA_CA_CERTS=/etc/ssl/certs/ca-certificates.crt
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s ../lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && npm install -g --no-fund --no-audit "playwright@${PLAYWRIGHT_VERSION}" \
    && playwright install --with-deps chromium \
    && rm -rf /var/lib/apt/lists/* /root/.npm
