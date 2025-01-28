SHELL := /bin/bash
.PHONY: *

OPTS=
ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
PHP=PHP_INI_SCAN_DIR=:$(ROOT_DIR)/config/sqlite/ php

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

phpdesktop: ## build phpdesktop release
	rm -rf build
	wget https://github.com/cztomczak/phpdesktop/releases/download/chrome-v130.1/phpdesktop-chrome-130.1-php-8.3.zip
	unzip phpdesktop-chrome-130.1-php-8.3.zip
	mv phpdesktop-chrome-130.1-php-8.3 build
	rm phpdesktop-chrome-130.1-php-8.3.zip
	cd build/www; rm -rf *
	cd build; rm -rf php/*
	cd build; mv phpdesktop-chrome.exe ChronicleKeeper.exe
	cd build/php; wget https://windows.php.net/downloads/releases/latest/php-8.4-nts-Win32-vs17-x64-latest.zip
	cd build/php; unzip php-8.4-nts-Win32-vs17-x64-latest.zip
	cd build/php; rm php-8.4-nts-Win32-vs17-x64-latest.zip

	git archive HEAD | (cd build/www; tar x)
	cd build/www; mv config/phpdesktop/php.ini ../php
	cd build/www; mv config/phpdesktop/settings.json ../
	cd build/www; APP_ENV=prod composer install --optimize-autoloader --no-dev --prefer-dist --no-progress
	cd build/www; APP_ENV=prod $(PHP) bin/console asset-map:compile
	cd build/www; APP_ENV=prod $(PHP) bin/console cache:warmup
	cd build/www; APP_ENV=prod $(PHP) bin/console app:db:init --force -vvv
	cd build/www; rm composer.lock composer.json

serve-symfony: ## start dev webserver with symfony cli
	PHP_INI_SCAN_DIR=:$(ROOT_DIR)/config/sqlite/ symfony local:server:start --no-tls

check-cs: ## check coding standards
	$(PHP) vendor/bin/phpcs -n

fix-cs: ## auto-fix coding standards
	$(PHP) vendor/bin/phpcbf -n

static-analysis: ## runs static analysis
	 $(PHP) vendor/bin/phpstan analyse -c phpstan.neon

phpunit: ## run phpunit
	APP_ENV=test $(PHP) bin/console cache:clear
	$(PHP) vendor/bin/phpunit --colors

coverage: ## run phpunit with generating coverage report
	XDEBUG_MODE=coverage $(PHP) vendor/bin/phpunit --coverage-html=coverage --coverage-clover=coverage.xml

lint-php: ## linting php files
	 if find src -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"; then exit 1; fi
	 if find tests -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"; then exit 1; fi

frontend: ## run symfony frontend build commands
	$(PHP) bin/console assets:install public
	$(PHP) bin/console importmap:install
	$(PHP) bin/console ux:icons:lock

rector: ## Exectute all rector rules
	$(PHP) vendor/bin/rector

init-db: reset-filesystem ## Initializes the database, forces recreation
	$(PHP) bin/console app:db:init --force -vvv

fix-all: ## fix all code issues
	make rector
	make fix-cs

reset-filesystem: ## reset of filesystem storage
	rm -rf var/cache/dev/library/*
	rm -rf var/data/document/*
	rm -rf var/data/image/*
	rm -rf var/data/database*
	rm -rf var/directories/*
	rm -rf var/documents/*
	rm -rf var/generated_images/images/*
	rm -rf var/generated_images/request/*
	rm -rf var/library/conversations/*
	rm -rf var/library/images/*
	rm -rf var/tmp/*
	rm -rf var/favorites.json
	rm -rf var/system_prompts.json

build: lint-php check-cs static-analysis phpunit
