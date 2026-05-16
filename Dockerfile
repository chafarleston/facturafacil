FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip netcat-openbsd \
    libpng-dev libonig-dev libxml2-dev libzip-dev \
    libcurl4-openssl-dev pkg-config libssl-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . .

RUN if [ -f "artisan" ]; then \
        chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true; \
        chmod -R 775 storage bootstrap/cache 2>/dev/null || true; \
    fi

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["php-fpm"]
