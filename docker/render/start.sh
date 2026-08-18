#!/bin/sh
set -e

# Runs at container start, not at build time, since Render only provides the
# database/app environment variables at runtime — caching them any earlier
# would bake in build-time placeholder values instead.
php artisan config:cache
php artisan route:cache
php artisan migrate --force

php-fpm -D
nginx -g "daemon off;"
