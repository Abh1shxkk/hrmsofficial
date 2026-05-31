#!/usr/bin/env bash
# Startup script for Railway deployment.
# Runs migrations, caches config/routes/views, then serves the app on Railway's $PORT.

set -e

# Cache config, routes and views for production performance.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Link storage so uploaded documents are publicly accessible.
php artisan storage:link || true

# Run database migrations (and seed demo data on first deploy).
php artisan migrate --force --seed

# Serve the application on the port Railway provides.
php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
