# syntax=docker/dockerfile:1

# Dependencias resueltas con el mismo PHP del runtime; el runtime no lleva Composer ni npm.
FROM php:8.2-cli AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --no-progress

FROM php:8.2-apache AS runtime

# Laravel 9 + MySQL. pdo_sqlite/openssl/mbstring/fileinfo ya vienen en la imagen base.
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-pescaderia.ini

COPY --from=vendor /app/vendor /var/www/html/vendor

WORKDIR /var/www/html
COPY . .

RUN mkdir -p public/img/productos public/img/negocio public/img/telas \
    && chown -R www-data:www-data storage bootstrap/cache public/img \
    && chmod +x docker/entrypoint.sh

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["apache2-foreground"]

# Producción: mismo runtime con OPcache congelado (el código viaja en la imagen).
FROM runtime AS prod

COPY docker/php-prod.ini /usr/local/etc/php/conf.d/99-prod.ini
