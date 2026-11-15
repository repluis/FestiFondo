FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git curl unzip zip libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci && npm run build && rm -rf node_modules

# Laravel necesita escribir en storage/ y bootstrap/cache/
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
# Quitar saltos de línea de Windows (CRLF) por si el archivo se editó en Windows
RUN sed -i 's/\r$//' /start.sh && chmod +x /start.sh

# Render usa PORT=10000 y Railway su propio PORT; start.sh lee $PORT
EXPOSE 8080

CMD ["/start.sh"]
