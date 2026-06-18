#!/bin/sh
set -e

if [ "$(id -u)" = "0" ]; then
    mkdir -p /var/www/html/storage/runtime /var/www/html/storage/logs /var/www/html/storage/certs /var/www/html/web/cpresources
    chown -R www-data:www-data /var/www/html/storage /var/www/html/web/cpresources
    exec gosu www-data "$0" "$@"
fi

mkdir -p /var/www/html/storage/runtime /var/www/html/storage/logs /var/www/html/storage/certs /var/www/html/web/cpresources

exec "$@"
