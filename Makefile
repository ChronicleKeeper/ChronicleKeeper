SHELL := /bin/bash
.PHONY: *

OPTS=
ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

phpdesktop: ## build phpdesktop release
	rm -rf build
	wget https://github.com/syracine69/phpdesktop/releases/download/chrome-v99.0-php7.4/php-desktop-chrome-99.0-rc-php-7.4.28.zip
	unzip php-desktop-chrome-99.0-rc-php-7.4.28.zip
	mv php-desktop-chrome-99.0-rc-php-7.4.28 build
	rm php-desktop-chrome-99.0-rc-php-7.4.28.zip
	cd build/www; rm -rf *
	cd build; rm -rf php/*
	cd build; mv php-desktop.exe ChronicleKeeper.exe
	cd build/php; wget https://windows.php.net/downloads/releases/latest/php-8.3-nts-Win32-vs16-x64-latest.zip
	cd build/php; unzip php-8.3-nts-Win32-vs16-x64-latest.zip
	cd build/php; rm php-8.3-nts-Win32-vs16-x64-latest.zip

	git archive HEAD | (cd build/www; tar x)
	cd build/www; mv config/phpdesktop/php.ini ../php
	cd build/www; mv config/phpdesktop/settings.json ../
	cd build/www; APP_ENV=prod composer install --optimize-autoloader --no-dev --prefer-dist --no-progress
	cd build/www; APP_ENV=prod php bin/console asset-map:compile
	cd build/www; APP_ENV=prod php bin/console cache:warmup
	cd build/www; rm composer.lock composer.json

serve-symfony: ## start dev webserver with symfony cli
	symfony local:server:start --no-tls

serve-frankenphp: ## start dev webserver with frankenphp cli
	PHP_INI_SCAN_DIR=$(ROOT_DIR)/config/phpdesktop/php.ini frankenphp php-server -l 127.0.0.1:8000 -r public

check-cs: ## check coding standards
	vendor/bin/phpcs -n

fix-cs: ## auto-fix coding standards
	vendor/bin/phpcbf -n

static-analysis: ## runs static analysis
	 vendor/bin/phpstan analyse -c phpstan.neon

phpunit: ## run phpunit
	APP_ENV=test php bin/console cache:clear
	 vendor/bin/phpunit --colors

coverage: ## run phpunit with generating coverage report
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-html=coverage --coverage-clover=coverage.xml

lint-php: ## linting php files
	 if find src -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"; then exit 1; fi
	 if find tests -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"; then exit 1; fi

frontend: ## run symfony frontend build commands
	php bin/console assets:install public
	php bin/console importmap:install
	php bin/console ux:icons:lock

rector: ## Exectute all rector rules
	php vendor/bin/rector

fix-all: ## fix all code issues
	make rector
	make fix-cs

build: lint-php check-cs static-analysis phpunit
