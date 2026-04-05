FROM php:8.2-apache

RUN a2dismod mpm_event && a2enmod mpm_prefork && docker-php-ext-install mysqli

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html
