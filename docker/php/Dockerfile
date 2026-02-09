FROM php:8.1-fpm-alpine

# Instalar extensiones de PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Instalar Composer (opcional)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html