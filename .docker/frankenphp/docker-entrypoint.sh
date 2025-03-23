#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    auth-config

    # Setup the dependencies if not done before, with a fresh dev setup, for example
    if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
        composer install --prefer-dist --no-progress --no-interaction
        # As this is a fresh install, initialize the database
        php bin/console app:db:init --force -vvv
    fi

    # If the container is started with with different environment variables, just refresh the cache on startup
    php bin/console cache:clear

    # Show information about the application
    php bin/console -V
fi

exec docker-php-entrypoint "$@"
