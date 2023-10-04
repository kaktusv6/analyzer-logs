FROM php:8.0-alpine as app

ENV PHP_MEMORY_LIMIT=512M

RUN apk update && apk add --no-cache libzip-dev zip linux-headers

RUN apk add --virtual build-dependencies --no-cache ${PHPIZE_DEPS}

# Install Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apk del build-dependencies

WORKDIR /var/www

COPY . .

RUN mv ./docker/configs/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN php composer.phar install

RUN chmod +x docker/deploy.sh

CMD ./docker/deploy.sh
