#!/bin/sh
set -eu

cd /var/www/html

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force --ansi
fi

php artisan package:discover --ansi
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

exec apache2-foreground
