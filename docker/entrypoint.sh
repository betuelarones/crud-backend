#!/bin/sh
set -eu

cd /var/www/html

wait_for_mysql() {
    php -r '
        $host = getenv("DB_HOST") ?: "mysql";
        $port = getenv("DB_PORT") ?: "3306";
        $database = getenv("DB_DATABASE") ?: "crud_backend";
        $username = getenv("DB_USERNAME") ?: "crud_user";
        $password = getenv("DB_PASSWORD") ?: "crud_password";

        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [PDO::ATTR_TIMEOUT => 3],
            );
            exit(0);
        } catch (Throwable $exception) {
            exit(1);
        }
    '
}

echo "Esperando a MySQL..."
until wait_for_mysql; do
    sleep 2
done

# Telescope no debe intentar escribir durante la preparación inicial de la base.
TELESCOPE_ENABLED=false
export TELESCOPE_ENABLED

if [ -z "${APP_KEY:-}" ]; then
    KEY_FILE="storage/.docker-app-key"

    if [ -f "$KEY_FILE" ]; then
        APP_KEY="$(cat "$KEY_FILE")"
    else
        APP_KEY="$(php artisan key:generate --show)"
        printf '%s' "$APP_KEY" > "$KEY_FILE"
    fi

    export APP_KEY
fi

mkdir -p \
    bootstrap/cache \
    storage/api-docs \
    storage/app \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data storage bootstrap/cache

php artisan package:discover --ansi
php artisan migrate --force

CATEGORY_COUNT="$(php -r '
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $database = getenv("DB_DATABASE") ?: "crud_backend";
    $username = getenv("DB_USERNAME") ?: "crud_user";
    $password = getenv("DB_PASSWORD") ?: "crud_password";

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$database}",
        $username,
        $password,
    );

    echo (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
')"

if [ "$CATEGORY_COUNT" = "0" ]; then
    php artisan db:seed --force
fi

php artisan l5-swagger:generate

chown -R www-data:www-data storage bootstrap/cache

TELESCOPE_ENABLED=true
export TELESCOPE_ENABLED

echo "API lista en http://localhost:8000"
exec "$@"
