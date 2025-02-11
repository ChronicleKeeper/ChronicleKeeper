FROM dunglas/frankenphp

RUN install-php-extensions \
	gd \
	intl \
    mbstring \
	zip \
	opcache \
    pdo \
    pdo_pgsql;

ENV PHP_INI_SCAN_DIR=":$PHP_INI_DIR/app.conf.d"

WORKDIR /app
COPY --link ./.docker/frankenphp/conf.d/app.ini $PHP_INI_DIR/app.conf.d/
