#!/usr/bin/env sh
# Container start-up: configure port/env, prepare SQLite + storage, migrate, cache, run Apache.
set -e
cd /var/www/html

# --- Port (Render sets $PORT) ---
PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# --- App URL: fall back to the URL Render assigns ---
if [ -z "${APP_URL}" ] && [ -n "${RENDER_EXTERNAL_URL}" ]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

# --- APP_KEY: Render's generateValue gives raw base64; Laravel expects the "base64:" prefix ---
if [ -z "${APP_KEY}" ]; then
    echo "ERROR: APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi
case "${APP_KEY}" in
    base64:*) ;;
    *) export APP_KEY="base64:${APP_KEY}" ;;
esac

# --- SQLite database file ---
DB_FILE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
mkdir -p "$(dirname "${DB_FILE}")"
[ -f "${DB_FILE}" ] || touch "${DB_FILE}"

# --- Writable dirs ---
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
         storage/framework/views storage/logs bootstrap/cache

php artisan storage:link --force
php artisan migrate --force

if [ "${SEED_DEMO_DATA}" = "true" ]; then
    php artisan db:seed --force   # idempotent: demo@example.com / password
fi

php artisan config:cache
php artisan view:cache
php artisan event:cache

# Everything above ran as root; hand runtime files to Apache's user.
chown -R www-data:www-data storage bootstrap/cache "$(dirname "${DB_FILE}")"

exec apache2-foreground
