FROM php:8.1-apache

RUN docker-php-ext-install pdo_sqlite

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads /var/www/html/data \
 && chown -R www-data:www-data /var/www/html/uploads /var/www/html/data \
 && chmod -R 0777 /var/www/html/uploads /var/www/html/data

EXPOSE 80