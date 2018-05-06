FROM php:7.2-cli

RUN apt-get update && \
    apt-get install libpng-dev libjpeg-dev --no-install-recommends -qy && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --enable-gd-native-ttf --with-png-dir=/usr/include --with-jpeg-dir=/usr/include && \
    docker-php-ext-install -j$(nproc) gd
