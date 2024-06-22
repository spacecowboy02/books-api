FROM php:8.2-fpm

# Install required extensions
RUN apt-get update \
    && apt-get install -y \
        git \
        libzip-dev \
        libpq-dev \
        unzip \
    && docker-php-ext-install zip pdo pdo_mysql pdo_pgsql \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/project

# Copy application source
COPY . /var/www/project

# Change ownership of project files
RUN chown -R www-data:www-data /var/www/project

CMD ["php-fpm"]