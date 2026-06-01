#!/bin/sh

# Salin .env jika belum ada
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Jalankan generator Swagger Laravel untuk memicu penulisan file JSON dokumentasi di awal
php artisan l5-swagger:generate --ansi

# Jalankan PHP-FPM di latar belakang
php-fpm -D

# Jalankan Nginx di depan (foreground)
nginx -g "daemon off;"
