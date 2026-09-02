#!/usr/bin/env bash
set -e

cd /var/www/html

# Generate an APP_KEY if one is not provided via env.
if [ -z "$APP_KEY" ]; then
    echo "APP_KEY not set - generating..."
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY=$APP_KEY" >> .env
fi
export APP_KEY="$APP_KEY"

# Ensure storage directories exist with correct ownership (Render uses ephemeral fs).
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache
mkdir -p storage/logs
touch storage/logs/laravel.log 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# Wait for database.
echo "Waiting for database..."
until php artisan db:show >/dev/null 2>&1; do
    sleep 2
done
echo "Database ready."

# Migrate (idempotent).
php artisan migrate --force

# Seed only when there are no users (MIGRATE_AND_SEED=true enables it).
if [ "${MIGRATE_AND_SEED:-false}" = "true" ]; then
    USERS=$(php artisan tinker --execute="echo App\\Models\\User::count();" 2>/dev/null || echo "0")
    if [ -z "$USERS" ] || [ "$USERS" = "0" ]; then
        echo "Seeding database..."
        php artisan db:seed --force
    else
        echo "Database already seeded ($USERS users) - skipping seed."
    fi
fi

# Clear/cache config (cached config references env vars, so run after env is set).
php artisan config:clear || true

echo "Starting nginx + php-fpm..."
php-fpm -D
exec nginx -g "daemon off;"