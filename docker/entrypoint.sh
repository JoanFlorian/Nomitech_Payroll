#!/bin/bash

# Wait for database if needed (optional but recommended in production)
# sleep 5

# Run migrations (force since it's production)
php artisan migrate --force

# Clear and cache config/routes/views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the application
# We use 0.0.0.0 to allow external access and port 10000 for Render compatibility
echo "Starting Laravel server on port 10000..."
php artisan serve --host=0.0.0.0 --port=10000
