# Usar imagen oficial de PHP con Apache
FROM php:8.1-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev

# Limpiar cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Configurar Apache para servir desde /var/www/html
RUN sed -i 's!/var/www/html!/var/www/html/frontend!g' /etc/apache2/sites-available/000-default.conf
RUN echo "Alias /backend /var/www/html/backend" >> /etc/apache2/sites-available/000-default.conf
RUN echo "<Directory /var/www/html/backend>" >> /etc/apache2/sites-available/000-default.conf
RUN echo "    Options Indexes FollowSymLinks" >> /etc/apache2/sites-available/000-default.conf
RUN echo "    AllowOverride All" >> /etc/apache2/sites-available/000-default.conf
RUN echo "    Require all granted" >> /etc/apache2/sites-available/000-default.conf
RUN echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf

# Crear estructura de directorios
RUN mkdir -p /var/www/html/backend /var/www/html/frontend

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Puerto expuesto
EXPOSE 80

# Comando de inicio
CMD ["apache2-foreground"]