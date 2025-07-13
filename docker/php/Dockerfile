FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install PHP extensions với explicit GD configuration
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        zip \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis

# CRITICAL: Verify GD installation
RUN php -m | grep -i gd || (echo "GD extension not found!" && exit 1)

# Create necessary directories với proper permissions
RUN mkdir -p /var/www/html/storage/logs \
    /var/www/html/storage/sessions \
    && chmod 755 /var/www/html/storage \
    && chmod 755 /var/www/html/storage/logs \
    && chmod 755 /var/www/html/storage/sessions

# Create user với same UID như host (với error handling)
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN if [ "$USER_ID" != "33" ]; then \
        groupmod -g $GROUP_ID www-data 2>/dev/null || true; \
        usermod -u $USER_ID -g www-data www-data 2>/dev/null || true; \
    fi

# Set proper ownership for storage directories  
RUN chown -R www-data:www-data /var/www/html/storage

# Set working directory
WORKDIR /var/www/html

# Copy entrypoint script
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Switch to non-root user for security
USER www-data

# Final verification that extensions are working
RUN php -r "if (!extension_loaded('gd')) { echo 'GD extension not loaded!'; exit(1); }"

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]