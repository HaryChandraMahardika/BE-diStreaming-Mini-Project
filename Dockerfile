# Gunakan image PHP 8.2 dengan Apache
FROM php:8.2-apache

# Install dependensi sistem dan ekstensi PHP yang dibutuhkan untuk Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql \
    && apt-get clean

# Install Composer (untuk Laravel)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Salin semua file aplikasi ke dalam container
COPY . .

# Install dependencies Laravel menggunakan Composer
RUN composer install --no-dev --optimize-autoloader

# Set permission agar bisa diakses oleh web server (Apache)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Jalankan Apache dalam foreground
CMD ["apache2-foreground"]