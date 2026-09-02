#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    echo "No .env found - copying from .env.docker"
    cp .env.docker .env
fi

# Ensure an application key is present (also covers mounted code where key may be empty).
if [ -z "$(grep '^APP_KEY=.\+' .env || true)" ]; then
    echo "Generating application key"
    php artisan key:generate --force
fi

# Wait for the database to accept connections.
echo "Waiting for database..."
until php artisan db:show >/dev/null 2>&1; do
    sleep 2
done
echo "Database ready."

# Run migrations (idempotent).
echo "Running migrations..."
php artisan migrate --force

# Seed only when the users table is empty, so restarts don't duplicate data.
USERS=$(php artisan tinker --execute="echo App\\Models\\User::count();" 2>/dev/null || echo "0")
if [ "$USERS" = "0" ] || [ -z "$USERS" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
else
    echo "Database already seeded ($USERS users) - skipping seed."
fi

# Cache config/routes for performance (optional, keep on for production env).
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

echo "Starting php-fpm..."
exec php-fpm