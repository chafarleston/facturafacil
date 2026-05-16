#!/bin/bash
echo "=== FacturaFacil Docker Setup ==="

if [ ! -d "vendor" ]; then
    echo "ERROR: vendor folder not found!"
    echo "Run 'composer install' first:"
    echo "  composer install --no-dev --optimize-autoloader"
    exit 1
fi

if [ ! -f ".env" ]; then
    cp .env.docker .env
    echo "✓ .env created"
fi

echo "→ Edit .env and set:"
echo "  PRINT_SERVER_URL=http://CLIENTE_IP:9100"
echo ""

docker compose up -d --build

echo ""
echo "=== Services ==="
echo "App:  http://IP_DEL_SERVIDOR"
echo ""
echo "If first run, migrate:"
echo "  docker compose exec php php artisan migrate --seed"
