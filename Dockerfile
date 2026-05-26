FROM php:8.4-fpm-alpine

# Install dependencies sistem
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    nodejs npm \
    && docker-php-ext-install pdo_pgsql mbstring gd zip bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci && npm run build \
    && php artisan storage:link

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
