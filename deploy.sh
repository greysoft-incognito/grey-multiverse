#!/bin/bash

echo "Pulling latest changes from Git..."
git pull origin main

echo "Installing dependencies..."
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader -n

echo "Running migrations..."
php artisan migrate --force

echo "Refreshing cache..."
php artisan optimize:clear
php artisan optimize

echo "Syncronizing Roles..."
php artisan app:sync-roles

echo "Setting permissions..."
chown -R greysoft-multiverse:greysoft-multiverse $(pwd)
chmod -R 775 storage bootstrap/cache

echo "Deployment complete."
