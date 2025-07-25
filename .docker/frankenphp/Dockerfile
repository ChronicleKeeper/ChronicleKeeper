FROM dunglas/frankenphp:1.9.0-php8.4 AS frankenphp_upstream

FROM frankenphp_upstream AS chronicle_keeper_base

WORKDIR /app
# VOLUME /app/var/

RUN install-php-extensions \
	@composer \
	gd \
	intl \
	mbstring \
	zip \
	opcache \
	pdo \
	pdo_pgsql \
;

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"
COPY --link ./.docker/frankenphp/conf.d/app.ini $PHP_INI_DIR/app.conf.d/
COPY --link ./.docker/frankenphp/Caddyfile /etc/caddy/Caddyfile
COPY --link --chmod=755 .docker/frankenphp/auth-config.sh /usr/local/bin/auth-config
COPY --link --chmod=755 ./.docker/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

HEALTHCHECK --start-period=60s CMD curl -f http://localhost:2019/metrics || exit 1
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile" ]

FROM chronicle_keeper_base AS chronicle_keeper_dev

# APP_ENV will be inherited from .env or can be overridden via environment variables
CMD [ "frankenphp", "run", "--config", "/etc/caddy/Caddyfile", "--watch" ]

FROM chronicle_keeper_base AS chronicle_keeper_prod

COPY --link ./.docker/frankenphp/conf.d/app.prod.ini $PHP_INI_DIR/app.conf.d/

# APP_ENV will be inherited from .env or can be overridden via environment variables
ENV FRANKENPHP_CONFIG=""
ENV BASIC_AUTH_USER=""
ENV BASIC_AUTH_PASSWORD=""

COPY --link composer.* symfony.* ./
RUN set -eux; \
	composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link . ./
RUN rm -Rf ./docker/frankenphp

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; \
	php bin/console asset-map:compile; \
	php bin/console cache:clear; \
	php bin/console cache:warmup; \
	sync;
