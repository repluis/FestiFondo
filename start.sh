#!/bin/sh
# Arranque del contenedor (Render y Railway).

# Render expone la URL pública en RENDER_EXTERNAL_URL; se usa como APP_URL si no se definió otra.
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

# Render y Railway indican el puerto en $PORT
PORT="${PORT:-8080}"

php artisan config:cache  || true
php artisan route:cache   || true
php artisan view:cache    || true
php artisan migrate --force || echo "[WARNING] Migrations failed"

exec php artisan serve --host=0.0.0.0 --port="$PORT"
