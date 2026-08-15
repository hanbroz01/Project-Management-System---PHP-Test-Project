# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install the mysqli extension (required for PHP to talk to MySQL/MariaDB)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your local source code into the container
COPY ./public /var/www/html/

# Set permissions if necessary
RUN chown -R www-data:www-data /var/www/html