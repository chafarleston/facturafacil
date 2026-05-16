#!/bin/bash
echo "=== FacturaFacil Docker Setup ==="

if [ ! -f .env ]; then
    cp .env.docker .env
    echo "✓ .env created"
fi

echo "→ Edit .env and set:"
echo "  PRINT_SERVER_URL=http://CLIENTE_IP:9100"
echo "  (IP del Windows donde corre el Print Server)"
echo ""

docker compose up -d --build

echo ""
echo "=== Services ==="
echo "App:  http://IP_DEL_SERVIDOR"
echo "DB:   localhost:3306"
echo ""
echo "Run: docker compose exec php php artisan key:generate"
echo "     docker compose exec php php artisan migrate --seed"
