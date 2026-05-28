#!/bin/sh
set -e

# Deteksi mode: hanya app/web container yang menjalankan migrate + seed
# Worker container melewati bagian ini agar tidak terjadi race condition.
SKIP_SETUP="${SKIP_SETUP:-false}"

if [ "$SKIP_SETUP" != "true" ]; then

    # Generate APP_KEY jika belum ada
    if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
        echo "Generating APP_KEY..."
        php artisan key:generate --force
    fi

    # Tunggu PostgreSQL siap menerima koneksi (max 30 detik)
    echo "Menunggu database siap..."
    i=0
    until php -r "
        \$host = getenv('DB_HOST') ?: '127.0.0.1';
        \$port = getenv('DB_PORT') ?: '5432';
        \$db   = getenv('DB_DATABASE') ?: 'smartstock_pro';
        \$user = getenv('DB_USERNAME') ?: 'postgres';
        \$pass = getenv('DB_PASSWORD') ?: 'secret';
        new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    " > /dev/null 2>&1; do
        i=$((i+1))
        if [ $i -ge 30 ]; then
            echo "Database tidak merespons setelah 30 detik. Abort."
            exit 1
        fi
        sleep 1
    done

    echo "Menjalankan database migrations..."
    php artisan migrate --force

    echo "Menjalankan database seeders..."
    php artisan db:seed --force

    # Buat symbolic link storage jika belum ada
    if [ ! -L "/var/www/html/public/storage" ]; then
        echo "Membuat symbolic link storage..."
        php artisan storage:link
    fi

    # Cache config + route untuk performa production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

fi

# Jalankan perintah utama container (php-fpm / artisan queue:work)
exec "$@"
