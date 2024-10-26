FROM php:8.3-apache

ARG WWW_USER=1000

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    default-mysql-client

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip curl intl

COPY vhost.conf /etc/apache2/sites-available/000-default.conf
COPY server-name.conf /etc/apache2/conf-available/

RUN a2enmod rewrite && a2enconf server-name

RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN groupadd --force -g $WWW_USER webapp
RUN useradd -ms /bin/bash --no-user-group -g $WWW_USER -u $WWW_USER webapp

COPY . /app

# Définir les permissions pour storage et bootstrap/cache
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

RUN composer install --no-dev --optimize-autoloader
RUN chown -R webapp:webapp /app

RUN apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

USER ${WWW_USER}

CMD ["bash", "-c", "php artisan migrate --force && php artisan key:generate && apache2-foreground"]