#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# How long to keep retrying the database before giving up and failing the
# deploy. Neon/Supabase pause after inactivity, so allow a generous window.
DB_READY_TIMEOUT=${DB_READY_TIMEOUT:-180}
READY_FLAG=/var/www/html/storage/framework/ready

log()  { echo "[start] $*"; }
fatal() { echo "[start] FATAL: $*" >&2; }

# ---------------------------------------------------------------------------
# 1. APP_KEY
# ---------------------------------------------------------------------------
# A malformed APP_KEY (a raw string rather than a base64-encoded 32-byte key)
# makes every request that touches encryption fail with "Unsupported cipher or
# incorrect key length" -> HTTP 500 on every page.
APP_KEY_OK=$(php -r 'if (isset($_SERVER["APP_KEY"]) && str_starts_with($_SERVER["APP_KEY"], "base64:")) { $d = base64_decode(substr($_SERVER["APP_KEY"], 7), true); echo ($d !== false && strlen($d) === 32) ? "yes" : "no"; } else { echo "no"; }')

if [ "$APP_KEY_OK" != "yes" ]; then
    if [ -z "${APP_KEY:-}" ]; then
        # Nothing set at all: generate one so the service still boots, but make
        # the consequence loud, because it changes on every restart and
        # invalidates all existing sessions and cookies.
        APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
        log "WARNING: APP_KEY not set - generated a temporary one. Set a permanent"
        log "         key in the Render dashboard (php artisan key:generate --show)."
        log "         Until you do, every restart logs everyone out."
    else
        # Set but malformed: refuse to boot. Silently replacing it would hide
        # the misconfiguration and quietly log out every user.
        fatal "APP_KEY is set but is not a base64-encoded 32-byte key."
        fatal "Expected format: base64: followed by 44 base64 characters."
        fatal "Generate a correct one with: php artisan key:generate --show"
        fatal "Then update APP_KEY in the Render dashboard and redeploy."
        exit 1
    fi
fi
export APP_KEY="$APP_KEY"

# ---------------------------------------------------------------------------
# 2. Storage
# ---------------------------------------------------------------------------
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
touch storage/logs/laravel.log 2>/dev/null || true

# Clear any ready flag left over from a previous boot: until the database is
# verified we are not ready, and nginx must not serve traffic.
rm -f "$READY_FLAG"

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# ---------------------------------------------------------------------------
# 3. Start the web server FIRST so the port opens
# ---------------------------------------------------------------------------
# Render fails a deploy if no port opens during its scan window. Binding now and
# gating requests on the ready flag (see nginx.conf) means an unreachable
# database produces an honest 503 instead of a 500 from a half-booted app.
php-fpm -D
nginx -g "daemon off;" &
NGINX_PID=$!

shutdown() {
    log "shutting down"
    nginx -s quit 2>/dev/null || true
    kill -QUIT "$(cat /run/php-fpm.pid 2>/dev/null || echo 0)" 2>/dev/null || true
    wait "$NGINX_PID" 2>/dev/null || true
    exit 0
}
trap shutdown TERM INT

# ---------------------------------------------------------------------------
# 4. Database, with a bounded deadline
# ---------------------------------------------------------------------------
log "waiting up to ${DB_READY_TIMEOUT}s for the database..."

DB_READY=0
ATTEMPT=0
DEADLINE=$(( SECONDS + DB_READY_TIMEOUT ))

while [ "$SECONDS" -lt "$DEADLINE" ]; do
    ATTEMPT=$(( ATTEMPT + 1 ))
    DBOUT=$(php -r '
        $dsn = "pgsql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE");
        // Mirror Laravel'"'"'s own connection so this probe cannot pass while
        // the app still fails (Neon requires TLS).
        if ($ssl = getenv("DB_SSLMODE")) { $dsn .= ";sslmode=" . $ssl; }
        try { new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD")); echo "PDO OK\n"; }
        catch (Throwable $e) { echo "PDO ERROR: " . $e->getMessage() . "\n"; }
    ' 2>&1)

    if echo "$DBOUT" | grep -q "PDO OK"; then
        DB_READY=1
        log "database reachable (after ${ATTEMPT} attempt(s))."
        break
    fi

    log "[db] attempt ${ATTEMPT}: $(echo "$DBOUT" | tail -n 1)"
    sleep 3
done

if [ "$DB_READY" -ne 1 ]; then
    fatal "database unreachable after ${ATTEMPT} attempts / ${DB_READY_TIMEOUT}s."
    fatal "Last error: $(echo "$DBOUT" | tail -n 1)"
    fatal "Check in the Render dashboard:"
    fatal "  - DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD"
    fatal "  - the database is not suspended (free tiers auto-pause when idle)"
    fatal "  - DB_SSLMODE matches your provider (Neon requires 'require')"
    fatal "Aborting so this deploy is marked failed rather than serving 500s."
    kill -KILL "$NGINX_PID" 2>/dev/null || true
    exit 1
fi

# ---------------------------------------------------------------------------
# 5. Migrations, with the same deadline
# ---------------------------------------------------------------------------
log "applying migrations..."
while true; do
    if MIG=$(php artisan migrate --force 2>&1); then
        log "migrations applied."
        break
    fi

    if [ "$SECONDS" -ge "$DEADLINE" ]; then
        fatal "migrations kept failing until the deadline:"
        echo "$MIG" | tail -n 20 >&2
        fatal "Aborting so this deploy is marked failed rather than serving 500s."
        kill -KILL "$NGINX_PID" 2>/dev/null || true
        exit 1
    fi

    log "[migrate] failed, retrying: $(echo "$MIG" | tail -n 1)"
    sleep 5
done

# ---------------------------------------------------------------------------
# 6. Seed (only when the admin account is missing or legacy users remain)
# ---------------------------------------------------------------------------
if [ "${MIGRATE_AND_SEED:-false}" = "true" ]; then
    ADMIN_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::where('email', 'admin')->count();" 2>/dev/null || echo "0")
    LEGACY_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::where('name', 'Owner Admin')->orWhere('email', 'moroboro@akmotorcycle.com')->count();" 2>/dev/null || echo "0")

    if [ -z "$ADMIN_COUNT" ] || [ "$ADMIN_COUNT" = "0" ] || [ -z "$LEGACY_COUNT" ] || [ "$LEGACY_COUNT" != "0" ]; then
        log "admin account missing or legacy users found - reseeding users..."
        php artisan tinker --execute="App\\Models\\User::query()->delete();" 2>/dev/null || true
        php artisan db:seed --force
    else
        log "users already seeded - skipping."
    fi
fi

# Cached config would pin stale env values, so clear it after env is settled.
php artisan config:clear >/dev/null 2>&1 || true

# Free-tier containers restart on every deploy and after idle spin-downs, so
# pruning here keeps the database cache/session tables from filling the disk.
log "pruning expired cache and stale session rows..."
php artisan maintenance:prune 2>&1 | sed 's/^/[prune] /' || log "prune skipped (non-fatal)."

# ---------------------------------------------------------------------------
# 7. Ready
# ---------------------------------------------------------------------------
touch "$READY_FLAG"
chown www-data:www-data "$READY_FLAG" 2>/dev/null || true
log "startup complete - serving traffic."

wait "$NGINX_PID"
