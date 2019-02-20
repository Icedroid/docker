FROM php:7-fpm-alpine

ENV TZ=Asia/Shanghai

RUN apk add --update --no-cache --virtual .ext-deps \
        libjpeg-turbo-dev \
        libwebp-dev \
        libpng-dev \
        freetype-dev \
        libmcrypt-dev \
        autoconf \
        g++ \
        make \
        openssl-dev \
        icu-dev

RUN \
    docker-php-ext-configure pdo_mysql && \
    docker-php-ext-configure opcache && \
    docker-php-ext-configure exif && \
    docker-php-ext-configure gd \
        --with-jpeg-dir=/usr/include \
        --with-png-dir=/usr/include \
        --with-webp-dir=/usr/include \
        --with-freetype-dir=/usr/include && \
    docker-php-ext-configure intl && \
    docker-php-ext-configure sockets && \
    docker-php-ext-configure bcmath && \
    docker-php-ext-install pdo_mysql opcache exif gd intl sockets bcmath

RUN \
    apk add --no-cache --virtual .mongodb-ext-build-deps openssl-dev pcre-dev
    
RUN \
    pecl install redis && \
    pecl install mongodb && \
    pecl install xdebug && \
    echo "xdebug.remote_enable=on\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_autostart=on\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_port=9100\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_handler=dbgp\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.remote_connect_back=1\n" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    apk del .mongodb-ext-build-deps && \
    pecl clear-cache && \
    docker-php-ext-enable redis mongodb xdebug && \
    docker-php-source delete

COPY ./php.conf /usr/local/etc/php/conf.d/php.conf