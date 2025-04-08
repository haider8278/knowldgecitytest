FROM php:8.3-apache

# Install PDO MySQL extension and other useful PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite for Laravel
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html

# Copy database folder to the container
COPY ./database/migrations /var/www/html/src/database/migrations

# Expose port 80
EXPOSE 80