#!/bin/bash

# Wait for DB
until nc -z -v -w30 mariadb 3306 2>/dev/null; do
    echo "Waiting for MariaDB..."
    sleep 3
done

# Wait for DB to be ready
sleep 5

# Run migrations if needed
php artisan migrate --force 2>/dev/null || true

# Clear and cache config
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

exec "$@"
