FROM php:8.4-fpm-alpine

# Dependensi sistem
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo_pgsql mbstring gd zip bcmath intl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Layer 1: Composer deps (cache-friendly — hanya copy manifest dulu)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Layer 2: NPM deps (cache-friendly)
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

# Layer 3: Source code lengkap
COPY . .

# Build frontend assets
RUN npm run build

# Composer post-install scripts (setelah code lengkap tersedia)
RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

# Create required storage directories and set permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/app/public \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]
