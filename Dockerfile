# Dockerfile pour une application PHP
FROM php:8.2-apache

WORKDIR /var/www/html

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copier les fichiers de l'application
COPY . .

# Configurer Apache
RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite