FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --ignore-platform-req=ext-bcmath \
    --ignore-platform-req=ext-gd \
    --ignore-platform-req=ext-intl \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        gosu \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libcurl4-openssl-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php/php.ini /usr/local/etc/php/conf.d/craft.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-craft.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

ARG AIVEN_CA_CERT
RUN mkdir -p storage/runtime storage/logs web/cpresources docker/certs \
    && if [ -n "$AIVEN_CA_CERT" ]; then \
        printf '%s\n' "$AIVEN_CA_CERT" > docker/certs/ca.pem; \
    fi \
    && if [ ! -s docker/certs/ca.pem ]; then \
        echo "ERROR: docker/certs/ca.pem is missing." >&2; \
        echo "Download it from Aiven Console and commit it, or pass AIVEN_CA_CERT as a Docker build-arg." >&2; \
        exit 1; \
    fi \
    && chown -R www-data:www-data storage web/cpresources

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
