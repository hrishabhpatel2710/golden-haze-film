#!/bin/sh
set -e

export PORT="${PORT:-8080}"

CERT_DIR="/var/www/html/docker/certs"
CERT_FILE="${CERT_DIR}/ca.pem"

install_ca_cert() {
    mkdir -p "$CERT_DIR"

    if [ -n "$AIVEN_CA_CERT" ]; then
        printf '%s\n' "$AIVEN_CA_CERT" > "$CERT_FILE"
    fi
}

mkdir -p /var/www/html/storage/runtime /var/www/html/storage/logs "$CERT_DIR" /var/www/html/web/cpresources
install_ca_cert
chown -R www-data:www-data /var/www/html/storage /var/www/html/web/cpresources

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

if [ ! -s "$CERT_FILE" ]; then
    echo "WARNING: Aiven CA certificate not found at $CERT_FILE" >&2
    echo "Add docker/certs/ca.pem to the repo or set the AIVEN_CA_CERT env var on Render." >&2
fi

exec "$@"
