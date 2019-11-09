ARG PHP_VERSION=7.2
ARG COMPOSER_VERSION=1.8

FROM composer:${COMPOSER_VERSION}
FROM php:${PHP_VERSION}-cli

RUN apt-get update && \
    apt-get install libmagickwand-dev --no-install-recommends -qy && \
    rm -rf /var/lib/apt/lists/* && \
    ln -s /usr/include/x86_64-linux-gnu/gmp.h /usr/include/gmp.h && \
    pecl install imagick && docker-php-ext-enable imagick
