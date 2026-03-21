FROM php:8.2-cli

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP
RUN docker-php-ext-install pdo pdo_mysql zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copier les fichiers de dépendances en premier (cache layer)
COPY composer.json composer.lock ./

# Installer les dépendances sans les scripts (pas de .env encore)
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-progress

# Copier le reste du projet
COPY . .

# Finaliser l'autoloader
RUN composer dump-autoload --optimize

# Copier .env.example si .env n'existe pas
RUN cp -n .env.example .env || true

# Générer la clé Laravel
RUN php artisan key:generate --force

# Permissions sur storage et cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
