ARG PHP_VERSION=8.1
ARG COMPOSER_VERSION=2

FROM composer:${COMPOSER_VERSION} as composer
FROM php:${PHP_VERSION}-cli

COPY --from=composer /usr/bin/composer /usr/bin/composer

RUN apt-get update && \
    apt-get install libpng-dev libjpeg-dev --no-install-recommends -qy && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --enable-gd-native-ttf --with-png-dir=/usr/include --with-jpeg-dir=/usr/include && \
    docker-php-ext-install -j$(nproc) gd
