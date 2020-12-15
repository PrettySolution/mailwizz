FROM php:7.2-apache
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libzip-dev \
        libc-client-dev \
        libkrb5-dev \
    && rm -r /var/lib/apt/lists/* \
    && docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-configure imap --with-kerberos --with-imap-ssl
    && docker-php-ext-install -j$(nproc) zip pdo_mysql imap