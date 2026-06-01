FROM php:8.3-fpm-alpine

# Install Nginx dan dependensi dasar
RUN apk update && apk add --no-cache \
    nginx \
    curl \
    libxml2-dev \
    oniguruma-dev

# Konfigurasi Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Pasang Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Salin berkas package/composer ke dalam kontainer
COPY composer*.json ./

# Jalankan instalasi composer (tanpa skrip agar tidak memicu artisan dulu)
RUN composer install --no-scripts --no-autoloader --no-dev

# Salin kode aplikasi secara penuh
COPY . .

# Buat autoload composer yang dioptimalkan
RUN composer dump-autoload --optimize

# Atur kepemilikan berkas agar dapat diakses Nginx dan PHP-FPM
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Ekspos port 3000
EXPOSE 3000

# Set entrypoint dan jalankan
RUN chmod +x docker/entrypoint.sh
ENTRYPOINT ["docker/entrypoint.sh"]
