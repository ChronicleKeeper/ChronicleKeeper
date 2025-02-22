#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

    # Configure authentication if in prod mode
    if [ "$APP_ENV" = "prod" ]; then
        auth-config
    fi

    php bin/console -V

fi

exec docker-php-entrypoint "$@"
