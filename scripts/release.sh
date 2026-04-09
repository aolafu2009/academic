#!/usr/bin/env sh
set -eu

echo "Running database migrations..."
php artisan migrate --force

# 仅在生产环境构建缓存，避免影响本地开发调试。
if [ "${APP_ENV:-}" = "production" ]; then
    echo "Clearing old caches..."
    php artisan optimize:clear

    echo "Building production caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan event:cache
fi
