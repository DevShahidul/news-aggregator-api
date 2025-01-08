FROM php:8.2-fpm-alpine

ARG user
ARG uid

# Install system dependencies
RUN apk update && apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    supervisor \
    shadow

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install exif \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install gd \
    && apk --no-cache add nodejs npm

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Create system user
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# Copy custom PHP configuration
COPY docker-compose/php/local.ini /usr/local/etc/php/conf.d/local.ini

USER $user
