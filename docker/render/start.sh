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

# Start PHP-FPM first (nginx proxies requests to it).
php-fpm -D

# Start nginx immediately so the container binds a port. Render marks deploys
# as failed if no port opens during its scan window, and a paused/unreachable
# database would otherwise block startup forever. DB work happens in the
# background and keeps retrying until the database is reachable.
(
    echo "Waiting for database..."
    DB_READY=0
    ATTEMPT=0
    while [ "$DB_READY" -ne 1 ]; do
        ATTEMPT=$((ATTEMPT+1))
        if OUTPUT=$(php artisan db:show 2>&1); then
            DB_READY=1
            echo "Database ready."
        else
            ERR=$(echo "$OUTPUT" | tail -n 1)
            echo "[db wait] attempt $ATTEMPT failed: $ERR"
            sleep 2
        fi
    done

    while true; do
        if MIG=$(php artisan migrate --force 2>&1); then
            echo "Migrations applied."
            break
        else
            ERR=$(echo "$MIG" | tail -n 1)
            echo "[migrate] failed - retrying in 10s: $ERR"
            sleep 10
        fi
    done

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
    php artisan config:clear >/dev/null 2>&1 || true

    echo "Startup complete."
)&

exec nginx -g "daemon off;"