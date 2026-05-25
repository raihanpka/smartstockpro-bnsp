#!/bin/sh
set -e

# Wait for database connection or other services if needed
# In alpine, typically we can just run the commands as laravel handles DB connection retries to some extent or fails fast.
# Let's run migrations automatically
echo "Menjalankan database migrations..."
php artisan migrate --force

# Seed the database
echo "Menjalankan database seeders..."
php artisan db:seed --force

# Storage link if missing
if [ ! -d "/var/www/html/public/storage" ]; then
    echo "Membuat symbolic link storage..."
    php artisan storage:link
fi

# Execute the main container command
exec "$@"
