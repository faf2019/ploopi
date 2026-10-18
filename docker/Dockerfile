# Ploopi — image applicative
# Apache + PHP avec les extensions nécessaires à Ploopi.
# Construit automatiquement par `docker compose up --build`.
# Trois étages : « base » (PHP + extensions), « prod » (code intégré à l'image,
# docker-compose.prod.yml) et « dev » (dépôt monté, docker-compose.yml). « dev »
# est le dernier : c'est l'étage construit quand aucune cible n'est demandée.
# Version de PHP : argument PHP_VERSION (8.5 par défaut, 8.2 testée aussi),
# fourni par docker-compose.yml depuis la variable d'environnement du même nom.

ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-apache AS base

# Extensions PHP utilisées par Ploopi.
# mbstring, curl, xml, xmlwriter, dom, iconv sont déjà compilées dans l'image
# officielle : les recompiler échoue (en-têtes oniguruma, libcurl, libxml2
# absents). On n'ajoute que celles qui manquent :
# mysqli (base), gd (vignettes et graphiques), zip (modules, archives),
# exif (métadonnées d'images), intl (internationalisation).
# unzip et git servent à Composer.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype-dev libjpeg62-turbo-dev libpng-dev libzip-dev libicu-dev \
        unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" mysqli gd zip exif intl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# php.ini de production (l'image n'en fournit aucun : display_errors y serait
# actif), et les réglages propres à Ploopi.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/ploopi.ini "$PHP_INI_DIR/conf.d/ploopi.ini"

# Composer : vendor/ n'est pas versionné, l'entrée l'installe au premier démarrage.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Le .htaccess du dépôt (copié depuis .htaccess_modele) doit s'appliquer.
RUN sed -ri 's/^(\s*)AllowOverride None$/\1AllowOverride All/' /etc/apache2/apache2.conf

# ————————————————— Production —————————————————
# Code et dépendances intégrés à l'image, en lecture seule pour Apache (www-data).
# Restent inscriptibles, en volumes : modules/ (installation de modules depuis le
# backoffice), la configuration et les documents. Voir docker/LISEZMOI.md.
FROM base AS prod

# opcache : intégré à PHP depuis 8.5, à activer sur les versions antérieures.
# mod_remoteip : Apache (et les journaux) voient l'adresse du visiteur, pas celle de Caddy.
RUN { php -m | grep -qi '^Zend OPcache$' || docker-php-ext-enable opcache; } \
    && a2enmod remoteip expires \
    && sed -ri 's/^expose_php\s*=.*/expose_php = Off/' "$PHP_INI_DIR/php.ini"
COPY docker/prod.ini "$PHP_INI_DIR/conf.d/zz-ploopi-prod.ini"
COPY docker/remoteip.conf /etc/apache2/conf-enabled/remoteip.conf

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --no-progress \
        --no-scripts --optimize-autoloader --ignore-platform-req=ext-memcached \
    && rm -rf /root/.composer
COPY . .
# Copie de référence des modules versionnés (system) : l'entrée la remet dans le
# volume modules/ à chaque démarrage, pour qu'une nouvelle image s'applique.
RUN cp .htaccess_modele .htaccess \
    && rm -rf docker tests .git* \
    && cp -a modules /opt/ploopi-modules \
    && chown -R root:root . && chmod -R a+rX,go-w .

COPY docker/ploopi-entrypoint-prod /usr/local/bin/ploopi-entrypoint
RUN chmod +x /usr/local/bin/ploopi-entrypoint

EXPOSE 80
ENTRYPOINT ["ploopi-entrypoint"]
CMD ["apache2-foreground"]

# ————————————————— Développement / essai —————————————————
FROM base AS dev

# Le dépôt est monté en volume sur /var/www/html : rien à copier ici.
COPY docker/ploopi-entrypoint /usr/local/bin/ploopi-entrypoint
RUN chmod +x /usr/local/bin/ploopi-entrypoint

EXPOSE 80
ENTRYPOINT ["ploopi-entrypoint"]
CMD ["apache2-foreground"]
