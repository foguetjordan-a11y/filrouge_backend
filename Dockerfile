FROM php:8.2-cli

# Dependances systeme
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier les dependances en premier (meilleur cache Docker)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-progress --no-dev

# Copier le reste du projet
COPY . .

# Finaliser l'autoloader
RUN composer dump-autoload --optimize --no-dev

# Permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

EXPOSE 8000

# Script de demarrage
CMD sh -c "[ -f .env ] || cp .env.example .env \
  && php artisan key:generate --force \
  && php artisan config:clear \
  && php artisan migrate --force \
  && php artisan serve --host=0.0.0.0 --port=8000"
