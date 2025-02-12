SHELL := /bin/bash
.DEFAULT_GOAL := help
.PHONY: *

# Import specialized makefiles
-include .makefile/Makefile.*

# Base configuration
ROOT_DIR := $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
DB ?= pgsql

# Database-specific PHP configurations
SQLITE_PHP = PHP_INI_SCAN_DIR=:$(ROOT_DIR)/config/sqlite/ php
PGSQL_PHP = docker compose exec php
PHP = $(if $(filter sqlite,$(DB)),$(PGSQL_PHP),$(SQLITE_PHP))

help: ## Show this help
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@echo ""
	@awk -F ':.*?## ' '/^[a-zA-Z_-]+:.*?## / { printf "  \033[36m%-30s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)
	@echo ""
	@echo "Use DB=sqlite to run commands with SQLite instead of PostgreSQL"

# Development commands
dev: setup-dev-env ## Start development environment (use DB=sqlite for SQLite)
ifeq ($(DB),sqlite)
	PHP_INI_SCAN_DIR=:$(ROOT_DIR)/config/sqlite/ symfony local:server:start -d --no-tls
else
	docker compose --profile dev up -d
	sleep 3
endif

make dev-stop: ## Stop development environment
ifeq ($(DB),sqlite)
	PHP_INI_SCAN_DIR=:$(ROOT_DIR)/config/sqlite/ symfony local:server:stop
else
	docker compose --profile dev down
endif

test-all: ## Run tests with both databases
	make test DB=sqlite
	make test DB=pgsql

# Quality assurance
qa: lint-php check-cs static-analysis ## Run all QA tools

# Build commands
build: qa test-all ## Run full build suite

