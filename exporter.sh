#!/bin/bash

USER__GROUP='www-data'

if [ -n "$1" ]; then
    USER__GROUP=$1
fi

if [[ "$USER__GROUP" == "--help" ]]; then
    echo "usage:    ./exporter.sh username:usergroup"
    echo "E.g:      ./exporter.sh www-data:www-data"
    exit 0
fi

echo "Exporting Data."
php artisan app:export -M

echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R ${USER__GROUP}:${USER__GROUP} $(pwd)

echo "Export Complete complete."
