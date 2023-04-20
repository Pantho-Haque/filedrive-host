#!/usr/bin/env bash
echo "Running composer"

composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate:fresh --force 

echo "Seeding Database..."
php artisan db:seed --force 


echo "Link the storage..."
php artisan storage:link --force 

