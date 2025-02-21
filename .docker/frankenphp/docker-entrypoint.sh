#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

    php bin/console -V
    php bin/console asset-map:compile

fi

exec docker-php-entrypoint "$@"
