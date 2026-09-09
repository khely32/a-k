#!/usr/bin/env bash
set -e

cd /var/www/html

# Generate an APP_KEY if one is not provided OR is invalid (must be a
# base64-encoded 32-byte key for AES-256-CBC). A wrong-length raw string set in
# Render's dashboard otherwise makes every request fail with "Unsupported cipher
# or incorrect key length".
APP_KEY_OK=$(php -r 'if (isset($_SERVER["APP_KEY"]) && str_starts_with($_SERVER["APP_KEY"], "base64:")) { $d = base64_decode(substr($_SERVER["APP_KEY"], 7), true); echo ($d !== false && strlen($d) === 32) ? "yes" : "no"; } else { echo "no"; }')
if [ "$APP_KEY_OK" != "yes" ]; then
    if [ -z "$APP_KEY" ]; then
        echo "APP_KEY not set - generating..."
    else
        echo "APP_KEY is invalid (wrong format/length) - generating a new one..."
    fi
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
    echo "[db] DB_HOST=$DB_HOST DB_PORT=$DB_PORT DB_DATABASE=$DB_DATABASE DB_USERNAME=$DB_USERNAME DB_PASSWORD_len=${#DB_PASSWORD}"
    DB_READY=0
    ATTEMPT=0
    while [ "$DB_READY" -ne 1 ]; do
        ATTEMPT=$((ATTEMPT+1))
        DBOUT=$(php -r 'try { new PDO("pgsql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); echo "PDO OK\n"; } catch (Throwable $e) { echo "PDO ERROR: ".$e->getMessage()."\n"; }' 2>&1)
        if echo "$DBOUT" | grep -q "PDO OK"; then
            DB_READY=1
            echo "Database ready."
        else
            echo "[db wait] attempt $ATTEMPT: $DBOUT"
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

    # Seed when the new 'admin' account does not exist yet. If the DB only has
    # legacy users (from the old seeder), remove them first (sales.user_id is
    # FK-set-null, so this is safe) and seed the current user scheme.
    if [ "${MIGRATE_AND_SEED:-false}" = "true" ]; then
        ADMIN_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::where('email', 'admin')->count();" 2>/dev/null || echo "0")
        if [ -z "$ADMIN_COUNT" ] || [ "$ADMIN_COUNT" = "0" ]; then
            echo "Admin account missing - clearing users and reseeding..."
            php artisan tinker --execute="App\\Models\\User::query()->delete();" 2>/dev/null || true
            php artisan db:seed --force
        else
            echo "Users already seeded (admin exists) - skipping seed."
        fi
    fi

    # Clear/cache config (cached config references env vars, so run after env is set).
    php artisan config:clear >/dev/null 2>&1 || true

    echo "Startup complete."
)&

exec nginx -g "daemon off;"