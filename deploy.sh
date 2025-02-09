#!/bin/bash

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan view:cache
php artisan app:sync-roles
