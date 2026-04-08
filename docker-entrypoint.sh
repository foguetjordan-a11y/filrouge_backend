#!/bin/sh
set -e

echo "==> Demarrage de l'application Laravel..."

# Generer la cle si absente
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "==> Generation de la cle APP_KEY..."
    php artisan key:generate --force
fi

# Vider le cache de config
echo "==> Nettoyage du cache..."
php artisan config:clear
php artisan cache:clear

# Attendre que MySQL soit pret (securite supplementaire)
echo "==> Attente de la base de donnees..."
MAX_TRIES=30
TRIES=0
until php -r "
    try {
        \$pdo = new PDO(
            'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
            getenv('DB_USERNAME'),
            getenv('DB_PASSWORD'),
            [PDO::ATTR_TIMEOUT => 3]
        );
        echo 'ok';
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null | grep -q ok; do
    TRIES=$((TRIES + 1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "ERREUR: MySQL non disponible apres ${MAX_TRIES} tentatives"
        exit 1
    fi
    echo "    MySQL pas encore pret (tentative $TRIES/$MAX_TRIES), attente 3s..."
    sleep 3
done
echo "==> Base de donnees connectee !"

# Executer les migrations
echo "==> Execution des migrations..."
php artisan migrate --force

# Lien storage
php artisan storage:link 2>/dev/null || true

# Permissions finales
chmod -R 775 storage bootstrap/cache

echo "==> Demarrage PHP-FPM et Nginx..."
php-fpm -D
nginx -g "daemon off;"
