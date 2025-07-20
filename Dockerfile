FROM php:8.3.3-apache
RUN docker-php-ext-install mysqli
RUN apt-get update && apt-get install -y libgmp-dev \
    && docker-php-ext-install gmp bcmath
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY ./public /var/www/html/
