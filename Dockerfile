# PHP-FPM 8.2 on Alpine with Composer and common extensions
FROM php:8.2-fpm-alpine

# System deps + PHP extensions required by Laravel
RUN apk add --no-cache \
    bash git unzip icu-dev libzip-dev oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql bcmath opcache zip \
    && rm -rf /var/cache/apk/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Default PHP-FPM cmd
CMD ["php-fpm", "-y", "/usr/local/etc/php-fpm.conf", "-R"]
