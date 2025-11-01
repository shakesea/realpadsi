# Gunakan image PHP resmi
FROM php:8.2-apache

# Install ekstensi dan Composer
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev zip unzip git curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip

# Copy file project ke container
WORKDIR /var/www/html
COPY . .

# Install dependencies Laravel
RUN curl -sS https://getcomposer.org/installer | php && \
    php composer.phar install --no-dev --optimize-autoloader

# Set permission untuk storage & bootstrap
RUN chmod -R 775 storage bootstrap/cache

# Jalankan Apache
EXPOSE 80
CMD ["apache2-foreground"]
