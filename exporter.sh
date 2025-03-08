#!/bin/bash

USER__GROUP='www-data'

if [ -n "$2" ]; then
    USER__GROUP=$2
fi

echo "Exporting Data."
php artisan app:export -M

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R ${USER__GROUP}:${USER__GROUP} $(pwd)

echo "Export Complete complete."
