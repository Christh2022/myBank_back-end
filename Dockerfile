FROM php:8.3-fpm-alpine

ARG DEBIAN_FRONTEND=noninteractive

# Installer les dépendances système nécessaires
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    libxml2-dev \
    bash \
    && docker-php-ext-install pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Installer Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

RUN chown www-data:www-data /var/www/config/jwt/private.pem
RUN chown www-data:www-data /var/www/config/jwt/public.pem

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www/var /var/www/vendor

EXPOSE 9000

CMD ["php-fpm"]
