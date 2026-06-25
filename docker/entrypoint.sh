#!/bin/sh

# Salin .env jika belum ada
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Pastikan file SQLite database ada dan bisa ditulis
touch database/database.sqlite
chmod 664 database/database.sqlite
chown www-data:www-data database/database.sqlite
chown www-data:www-data database/

# Jalankan migrasi database agar tabel roles dan users tersedia
php artisan migrate --force

# Jalankan generator Swagger Laravel untuk memicu penulisan file JSON dokumentasi di awal
php artisan l5-swagger:generate --ansi

# Jalankan PHP-FPM di latar belakang
php-fpm -D

# Jalankan Nginx di depan (foreground)
nginx -g "daemon off;"
