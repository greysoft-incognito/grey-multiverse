#!/bin/bash

USER__GROUP='www-data'
dataset=()

helper() {
    echo "usage:            ./exporter.sh username:usergroup [<dataset>...]"
    echo "E.g:              ./exporter.sh www-data:www-data forms users"
    echo "Allowed Dataset:  forms, users, appointment and companies"
    echo "If you do not want to assign usergroup, set it as a period (.)"
    exit 0
}

args=("$@")
for i in "${!args[@]}"; do
    if [[ "${args[i]}" == "--help" ]]; then
        helper
        break
    elif [[ "$i" == 0 ]]; then
        USER__GROUP="${args[i]}"
    fi

    if [[ "$i" > 0 ]]; then
        dataset+=(${args[i]})
    fi
done

echo ${dataset[*]}

if [[ "${#dataset[@]}" > 0 ]]; then
    cmd="php artisan app:export ${dataset[*]}"
    echo "Exporting ${#dataset[@]} Dataset."
else
    cmd="php artisan app:export -M"
fi

echo "Exporting Data."

output=$(eval "$cmd" 2>&1)
exit_code=$?

if [[ $exit_code == 0 ]]; then
    echo "Setting permissions..."
    chmod -R 775 storage bootstrap/cache

    if [[ "$USER__GROUP" != '.' ]]; then
        if [[ "$USER__GROUP" == *:* ]]; then
            chown -R ${USER__GROUP} $(pwd)
        else
            chown -R ${USER__GROUP}:${USER__GROUP} $(pwd)
        fi
    fi

    echo "Export Complete complete."
else
    echo "$output" | grep '.' | head -n 3 | tr '\n' ' ' | tr -s '[:space:]' ' '
fi
