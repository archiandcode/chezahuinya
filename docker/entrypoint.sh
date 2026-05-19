#!/usr/bin/env sh
set -e

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan view:cache
fi

exec "$@"
