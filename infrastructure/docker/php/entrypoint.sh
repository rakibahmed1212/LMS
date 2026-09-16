#!/bin/sh
set -e

if [ ! -d vendor ]; then
    if [ "$APP_ENV" = "production" ]; then
        composer install --no-interaction --optimize-autoloader --no-dev
    else
        composer install --no-interaction --optimize-autoloader
    fi
fi

php artisan storage:link 2>/dev/null || true

if [ "$RUN_MIGRATIONS" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
