#!/bin/sh
# Arranque del contenedor: espera a MySQL, migra, siembra una base vacía y lanza Apache.
set -e

cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
    APP_KEY="$(php artisan key:generate --show)"
    export APP_KEY
    echo "entrypoint: APP_KEY vacía; se usa una clave efímera. Define APP_KEY en .env para conservarla."
fi

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "entrypoint: esperando a MySQL en ${DB_HOST:-sastreria-db}:${DB_PORT:-3306}..."
    attempts=0
    # shellcheck disable=SC2016  # el código PHP va literal: las variables las resuelve PHP.
    until php -r '
        $host = getenv("DB_HOST") ?: "sastreria-db";
        $port = getenv("DB_PORT") ?: "3306";
        $name = getenv("DB_DATABASE") ?: "sastreria";
        try {
            new PDO("mysql:host={$host};port={$port};dbname={$name}", getenv("DB_USERNAME") ?: "root", getenv("DB_PASSWORD") ?: "");
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge 60 ]; then
            echo "entrypoint: MySQL no respondió tras 120 segundos." >&2
            exit 1
        fi
        sleep 2
    done
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/img/productos public/img/negocio public/img/telas
chown -R www-data:www-data storage bootstrap/cache public/img 2>/dev/null || true

php artisan config:clear --no-interaction >/dev/null 2>&1 || true
php artisan package:discover --ansi

if [ "${APP_ENV:-local}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
fi

php artisan migrate --force

if [ "${SEED_ON_START:-true}" = "true" ]; then
    # shellcheck disable=SC2016  # el código PHP va literal: las variables las resuelve PHP.
    usuarios="$(php -r '
        $host = getenv("DB_HOST") ?: "sastreria-db";
        $port = getenv("DB_PORT") ?: "3306";
        $name = getenv("DB_DATABASE") ?: "sastreria";
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name}", getenv("DB_USERNAME") ?: "root", getenv("DB_PASSWORD") ?: "");
        echo (int) $pdo->query("select count(*) from usuario")->fetchColumn();
    ')"
    if [ "$usuarios" = "0" ]; then
        echo "entrypoint: base de datos sin usuarios; ejecutando db:seed."
        php artisan db:seed --force
    fi
fi

# Los comandos artisan de arriba corren como root y pueden dejar archivos de caché
# o logs propiedad de root; Apache (www-data) fallaría al sobrescribirlos.
chown -R www-data:www-data storage bootstrap/cache public/img 2>/dev/null || true

exec "$@"
