# Use PHP 8.2 CLI as base (Render works well with CLI + artisan serve or a proper web server like Nginx)
# For a more robust production setup, we'd use php:8.2-fpm + nginx, 
# but for simplicity and following the advice given, we'll use a CLI setup with artisan serve.
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd bcmath

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node.js & NPM (for Vite build)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install JS dependencies and build assets
RUN npm install && npm run build && rm -f public/hot

# Ensure storage and bootstrap/cache are writable
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /app

# Expose the port Render expects (default 10000)
EXPOSE 10000

# Use a custom entrypoint script to handle migrations and start the server
RUN chmod +x docker/entrypoint.sh
ENTRYPOINT ["docker/entrypoint.sh"]
