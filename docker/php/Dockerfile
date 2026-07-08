FROM php:8.2-apache

# Installation des extensions PHP nécessaires (y compris GD pour les images)
RUN apt-get update && apt-get install -y \
    git zip unzip curl libicu-dev libonig-dev libxml2-dev \
    libwebp-dev libjpeg62-turbo-dev libpng-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_mysql intl mbstring xml gd \
    && apt-get clean

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Activation du module Apache rewrite (pour les URLs Symfony)
RUN a2enmod rewrite

# Configuration Apache pour Symfony
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Activer OpCache pour accélérer PHP
RUN docker-php-ext-install opcache

# Configuration OpCache
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=20000\n\
opcache.revalidate_freq=0\n\
opcache.validate_timestamps=1" \
> /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html