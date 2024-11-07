# Build stage
FROM php:8.2-fpm-alpine AS builder

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    linux-headers \
    $PHPIZE_DEPS \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Enable opcache
RUN docker-php-ext-enable opcache

# Copy opcache configuration
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy entire project
COPY . .

# Install dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Generate Laravel optimization files (API specific)
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan event:cache

# Production stage
FROM php:8.2-fpm-alpine

# Install production dependencies
RUN apk add --no-cache \
    libpng \
    libzip \
    oniguruma

# Copy PHP extensions and configs from builder
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Set working directory
WORKDIR /var/www

# Copy application from builder
COPY --from=builder /var/www .

# Create system user
RUN addgroup -g 1000 laravel && \
    adduser -u 1000 -G laravel -h /var/www -s /bin/sh -D laravel

# Set proper permissions
RUN chown -R laravel:laravel /var/www/storage /var/www/bootstrap/cache

# Switch to non-root user
USER laravel

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]