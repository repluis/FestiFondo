#!/bin/sh

php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan migrate --force || echo "[WARNING] Migrations failed"
php artisan serve --host=0.0.0.0 --port=8080
