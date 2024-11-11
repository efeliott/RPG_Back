# Utilise l'image officielle PHP avec Apache et spécifie la version de PHP
FROM php:8.3-apache

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mbstring xml pdo_mysql

# Installer Redis via PECL et activer l'extension
RUN pecl install redis && docker-php-ext-enable redis

# Activer mod_rewrite pour Laravel
RUN a2enmod rewrite

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www

# Copier le code source Laravel dans le conteneur
COPY . /var/www

# Configurer Apache pour pointer vers le répertoire public de Laravel
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/apache2.conf

# Donner les permissions nécessaires aux dossiers de stockage et de cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Exposer le port 80 pour Apache
EXPOSE 80

# Commande de démarrage d'Apache
CMD ["apache2-foreground"]