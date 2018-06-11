FROM php:7.2-apache
COPY . /var/www/html/

WORKDIR /var/www/html

RUN apt-get update
RUN apt-get upgrade -y
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite