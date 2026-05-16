#!/bin/bash

if [ ! -d "vendor" ]; then
    echo "ERROR: vendor folder not found. Run 'composer install' before building Docker."
    echo "       Or copy your local vendor directory."
    exit 1
fi

if [ ! -f ".env" ]; then
    if [ -f ".env.docker" ]; then
        cp .env.docker .env
        echo "✓ .env created from .env.docker"
    else
        echo "ERROR: .env not found"
        exit 1
    fi
fi

if [ -z "$(grep APP_KEY .env | cut -d= -f2)" ] || [ "$(grep APP_KEY .env | cut -d= -f2)" = "" ]; then
    php artisan key:generate --force 2>/dev/null || true
fi

until nc -z -v -w30 mariadb 3306 2>/dev/null; do
    echo "Waiting for MariaDB..."
    sleep 3
done

sleep 3

php artisan migrate --force 2>/dev/null || echo "Migrate skipped or failed"

php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

exec "$@"
