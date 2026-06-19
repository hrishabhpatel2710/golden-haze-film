#!/bin/sh
set -e

export PORT="${PORT:-8080}"

CERT_DIR="/var/www/html/docker/certs"
CERT_FILE="${CERT_DIR}/ca.pem"
PHP_FPM_ENV_CONF="/usr/local/etc/php-fpm.d/zzz-env.conf"

install_ca_cert() {
    mkdir -p "$CERT_DIR"

    if [ -n "$AIVEN_CA_CERT" ]; then
        printf '%s\n' "$AIVEN_CA_CERT" > "$CERT_FILE"
    fi
}

write_php_fpm_env() {
  : > "$PHP_FPM_ENV_CONF"
  echo "; Generated at container start from runtime environment" >> "$PHP_FPM_ENV_CONF"

  env | cut -d= -f1 | while IFS= read -r var; do
    case "$var" in
      CRAFT_*|PRIMARY_*|SYSTEM_*|SMTP_*|APP_ID|DATABASE_URL)
        echo "env[$var] = \$$var" >> "$PHP_FPM_ENV_CONF"
        ;;
    esac
  done
}

check_db_host() {
  if [ -z "$CRAFT_DB_SERVER" ]; then
    echo "WARNING: CRAFT_DB_SERVER is not set. Add database env vars in Render." >&2
    return 0
  fi

  if ! getent hosts "$CRAFT_DB_SERVER" >/dev/null 2>&1; then
    echo "WARNING: Cannot resolve database host '$CRAFT_DB_SERVER'." >&2
    echo "Check that your Aiven MySQL service is running and copy the host from Aiven Console." >&2
    echo "Also verify Aiven allowed inbound IPs includes Render (use 0.0.0.0/0 for testing)." >&2
  fi
}

mkdir -p /var/www/html/storage/runtime /var/www/html/storage/logs "$CERT_DIR" /var/www/html/web/cpresources
install_ca_cert
write_php_fpm_env
check_db_host
chown -R www-data:www-data /var/www/html/storage /var/www/html/web/cpresources

envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

if [ ! -s "$CERT_FILE" ]; then
    echo "WARNING: Aiven CA certificate not found at $CERT_FILE" >&2
    echo "Add docker/certs/ca.pem to the repo or set the AIVEN_CA_CERT env var on Render." >&2
fi

exec "$@"
