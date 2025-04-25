# Use the base PHP image
FROM php:8.1-apache

# Install MySQLi extension
RUN docker-php-ext-install mysqli

# Enable Apache mod_rewrite (optional, if needed for your PHP app)
RUN a2enmod rewrite