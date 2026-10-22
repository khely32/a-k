#!/usr/bin/env bash
# NOTE: "set -e" is deliberately NOT used here.
#
# This script used to kill nginx with SIGKILL and exit 1 when the database did
# not answer. That was the direct cause of ERR_INVALID_RESPONSE in the
# browser: SIGKILL cuts off whatever nginx was in the middle of writing, so
# the client received a truncated HTTP response, and the container exit made
# Render answer with 502 / "hibernate-wake-error" instead of a real response.
#
# Every command below is therefore checked explicitly, the container always
# stays up, and the database is retried for as long as it takes. When the
# database comes back the site starts serving on its own - no redeploy.
set -uo pipefail

cd /var/www/html

# How long to keep reporting database problems at full volume. The container
# never gives up on the database, it just gets quieter about it.
DB_READY_TIMEOUT=${DB_READY_TIMEOUT:-180}
# How long to wait between retries once the initial window has passed.
DB_RETRY_INTERVAL=${DB_RETRY_INTERVAL:-15}
READY_FLAG=/var/www/html/storage/framework/ready

log()   { echo "[start] $*"; }
warn()  { echo "[start] WARNING: $*" >&2; }
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
    log "shutting down (draining in-flight requests)..."

    # SIGQUIT is nginx's graceful stop: it finishes the responses it is
    # already sending. SIGKILL would cut them off mid-body and the browser
    # would report ERR_INVALID_RESPONSE instead of a clean close.
    kill -QUIT "$NGINX_PID" 2>/dev/null || nginx -s quit 2>/dev/null || true

    # php-fpm is daemonised, so it is not a child of this script and would
    # otherwise be left behind as an orphan.
    for PIDFILE in /run/php-fpm.pid /usr/local/var/run/php-fpm.pid; do
        if [ -f "$PIDFILE" ]; then
            kill -QUIT "$(cat "$PIDFILE" 2>/dev/null)" 2>/dev/null || true
        fi
    done

    wait "$NGINX_PID" 2>/dev/null || true
    exit 0
}
trap shutdown TERM INT

# ---------------------------------------------------------------------------
# 4. Database - retry forever, never crash
# ---------------------------------------------------------------------------
# The old behaviour killed nginx and exited when the database did not answer.
# Now the container stays up, keeps answering with a well-formed 503 +
# Retry-After, and keeps retrying, so the site recovers by itself as soon as
# the database is reachable again.
log "waiting for the database..."

DB_READY=0
ATTEMPT=0
DEADLINE=$(( SECONDS + DB_READY_TIMEOUT ))

while true; do
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

    if [ "$SECONDS" -ge "$DEADLINE" ]; then
        warn "database unreachable after ${ATTEMPT} attempts / ${DB_READY_TIMEOUT}s."
        warn "Last error: $(echo "$DBOUT" | tail -n 1)"
        warn "Check in the Render dashboard:"
        warn "  - DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD"
        warn "  - the database is not suspended (free tiers auto-pause when idle)"
        warn "  - the database is not over its storage/compute quota"
        warn "  - DB_SSLMODE matches your provider (Neon requires 'require')"
        warn "The container stays up and keeps serving 503 + Retry-After."
        warn "Retrying every ${DB_RETRY_INTERVAL}s - the site will recover on its own."
        DEADLINE=$(( SECONDS + 60 ))
        sleep "$DB_RETRY_INTERVAL"
        continue
    fi

    log "[db] attempt ${ATTEMPT}: $(echo "$DBOUT" | tail -n 1)"
    sleep 3
done

# ---------------------------------------------------------------------------
# 5. Migrations, retried until they succeed
# ---------------------------------------------------------------------------
DEADLINE=$(( SECONDS + DB_READY_TIMEOUT ))
log "applying migrations..."

while true; do
    if MIG=$(php artisan migrate --force 2>&1); then
        log "migrations applied."
        break
    fi

    if [ "$SECONDS" -ge "$DEADLINE" ]; then
        warn "migrations kept failing until the deadline:"
        echo "$MIG" | tail -n 20 >&2
        warn "The container stays up and keeps serving 503 + Retry-After."
        warn "Retrying every ${DB_RETRY_INTERVAL}s - the site will recover on its own."
        DEADLINE=$(( SECONDS + 60 ))
        sleep "$DB_RETRY_INTERVAL"
        continue
    fi

    log "[migrate] failed, retrying: $(echo "$MIG" | tail -n 1)"
    sleep 5
done

# ---------------------------------------------------------------------------
# 6. Seed (only when the admin account is missing or legacy users remain)
# ---------------------------------------------------------------------------
if [ "${MIGRATE_AND_SEED:-false}" = "true" ]; then
    SEEDED=0

    for TRY in 1 2 3; do
        ADMIN_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::where('email', 'admin')->count();" 2>/dev/null || echo "0")
        LEGACY_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::where('name', 'Owner Admin')->orWhere('email', 'moroboro@akmotorcycle.com')->count();" 2>/dev/null || echo "0")

        if [ -z "$ADMIN_COUNT" ] || [ "$ADMIN_COUNT" = "0" ] || [ -z "$LEGACY_COUNT" ] || [ "$LEGACY_COUNT" != "0" ]; then
            log "admin account missing or legacy users found - reseeding users (attempt ${TRY})..."
            php artisan tinker --execute="App\\Models\\User::query()->delete();" 2>/dev/null || true
            if php artisan db:seed --force; then
                SEEDED=1
                break
            fi
            log "[seed] failed, retrying in 10s..."
            sleep 10
        else
            log "users already seeded - skipping."
            SEEDED=1
            break
        fi
    done

    if [ "$SEEDED" -ne 1 ]; then
        warn "seeding did not complete - the admin account may be missing."
        warn "Run 'php artisan db:seed --force' once the database is reachable."
    fi
fi

# Cached config/routes/views would pin stale values, so clear them now that
# the environment is settled.
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear  >/dev/null 2>&1 || true
php artisan view:clear   >/dev/null 2>&1 || true

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
EXIT_CODE=$?
log "nginx exited (code ${EXIT_CODE})."
exit "$EXIT_CODE"
