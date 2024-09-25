.PHONY: *

OPTS=

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
	cd build/php; wget https://windows.php.net/downloads/releases/latest/php-8.3-nts-Win32-vs16-x86-latest.zip
	cd build/php; unzip php-8.3-nts-Win32-vs16-x86-latest.zip
	cd build/php; rm php-8.3-nts-Win32-vs16-x86-latest.zip

	git archive HEAD | (cd build/www; tar x)
	cd build/www; mv config/phpdesktop/php.ini ../php
	cd build/www; mv config/phpdesktop/settings.json ../
	cd build/www; APP_ENV=prod composer install --optimize-autoloader --no-dev --prefer-dist --no-progress
	cd build/www; APP_ENV=prod php bin/console asset-map:compile
	cd build/www; APP_ENV=prod php bin/console cache:warmup
	cd build/www; rm composer.lock composer.json

serve-web: ## start dev webserver
	symfony local:server:start --no-tls

check-cs: ## check coding standards
	vendor/bin/phpcs -n

fix-cs: ## auto-fix coding standards
	vendor/bin/phpcbf -n

static-analysis: ## runs static analysis
	 vendor/bin/phpstan analyse -c phpstan.neon

phpunit: ## run phpunit
	APP_ENV=test php bin/console cache:clear
	 vendor/bin/phpunit

lint-php: ## linting php files
	 if find src -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"; then exit 1; fi
	 if find tests -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"; then exit 1; fi

frontend: ## run symfony frontend build commands
	php bin/console assets:install public
	php bin/console importmap:install
	php bin/console ux:icons:lock


build: lint-php check-cs static-analysis phpunit
