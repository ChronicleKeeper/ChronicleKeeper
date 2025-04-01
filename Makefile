SHELL := /bin/bash
.DEFAULT_GOAL := help
.PHONY: *

# Import specialized makefiles
-include .makefile/Makefile.*

# Base configuration
ROOT_DIR := $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
PHP = docker compose exec php

help: ## Show this help
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@echo ""
	@awk -F ':.*?## ' '/^[a-zA-Z_-]+:.*?## / { printf "  \033[36m%-30s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)

# Development commands
dev: setup-dev-env ## Start development environment
	docker compose --profile dev up -d

make dev-stop: ## Stop development environment
	docker compose --profile dev down

reset: ## Reset the environment and deletes the linked containers
	rm -rf var/settings.json var/data/* var/cache/* var/log/* var/tmp/*
	docker compose --profile all down -v

# Quality assurance
qa: lint-php check-cs static-analysis ## Run all QA tools

# Build commands
build: qa test-all ## Run full build suite

