#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ]; then
    auth-config
fi

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    # Setup the dependencies if not done before, with a fresh dev setup, for example
    if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
        composer install --prefer-dist --no-progress --no-interaction

        # For dev and prod environments, initialize the database if needed
        if [ "$APP_ENV" != "test" ]; then
            php bin/console app:db:init --force -vvv
        fi
    fi
fi

exec docker-php-entrypoint "$@"
