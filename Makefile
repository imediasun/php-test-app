#!/usr/bin/make

.PHONY: help shell
.PHONY: install install-php
.PHONY: up-db up-php build-php up-web up-kafka
.PHONY: up down setup restart upgrade clean

.DEFAULT_GOAL : help
.SHELLFLAGS = -exc
SHELL := /bin/bash


RAND := $(shell bash -c 'echo $$((RANDOM % 1000))' )

GO_PIPELINE_COUNTER ?= 1
GO_STAGE_COUNTER ?= ${RAND}

# This will output the help for each task. thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
help: ## Show this help
	@printf "\033[33m%s:\033[0m\n" 'Available commands'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-18s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: install-php

install-php:
	docker-compose run --rm --no-deps -T php composer install
	docker-compose run --rm --no-deps -T php vendor/bin/phinx migrate
	docker-compose run --rm --no-deps -T consumer composer install


shell: ## Start shell into chat-php container
	docker-compose exec php bash


up-db: ## Create and start containers
	docker-compose up -d db

up-php: ## Create and start containers
	docker-compose up -d php

build-php: ## Create and start containers
	docker-compose build php

up-web: ## Create and start containers
	docker-compose up -d web

up-kafka: ## Create and start containers
	docker-compose up -d kafka

up: ## Create and start containers
	docker-compose up -d

down: ## Stop containers
	docker-compose down

setup: up install ## Create and start containers

restart: down up ## Restart all containers

upgrade: ## Create and start containers
	make build-php
	make install
	make up-php


