# FILEPATH: coverage-grader-backend/Dockerfile
# A simplified, more robust single-stage Dockerfile for Laravel

# STAGE 1: Base image with PHP and essential extensions
FROM php:8.4-fpm-alpine AS base
ENV DEBIAN_FRONTEND=noninteractive
WORKDIR /var/www

# Install essential packages and PHP extension dependencies
# Using apk for Alpine Linux
# ADDED netcat-openbsd to wait for database connection
RUN apk add --no-cache \
    netcat-openbsd \
    git curl zip unzip libzip-dev libpng-dev libjpeg-turbo-dev \
    freetype-dev libwebp-dev postgresql-dev icu-dev \
    oniguruma-dev \
    build-base autoconf bash shadow && \
    # Install PHP extensions
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath zip intl && \
    # Install redis extension via PECL
    pecl install redis && docker-php-ext-enable redis

# Copy custom PHP configuration
COPY php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Get the latest Composer binary
COPY --from=composer:2.8.12 /usr/bin/composer /usr/bin/composer

# Define a build argument for development dependencies
ARG INSTALL_DEV=false

# Increase Composer's timeout limit
RUN composer config --global process-timeout 2000

# Copy the entire application source code
COPY . .

# Install dependencies based on the build argument
RUN if [ ${INSTALL_DEV} = true ]; then \
        # For local dev, install all packages
        composer install --no-interaction --optimize-autoloader; \
    else \
        # For production, install only prod packages
        composer install --no-interaction --no-dev --optimize-autoloader; \
    fi

# Copy and set permissions for the entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Set the entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command to run
CMD ["php-fpm"]
