## Everything runs inside the dev container (.devcontainer), no local PHP needed.
##
## Variables:
##   PHP_VERSION=8.4     PHP version of the dev container (8.2, 8.3, 8.4, 8.5)
##   SYMFONY=7.4         Pin all symfony/* packages on "make update" (7.4, 8.0, 8.1, ...)
##   COMPOSER_FLAGS=     Extra flags for "make install/update" (e.g. --prefer-lowest)
##   c=                  Command for "make composer" / "make php" (e.g. c="require foo/bar")
##   MATRIX=             PHP/Symfony pairs for "make matrix"

PHP_VERSION ?= 8.4
SYMFONY ?=
COMPOSER_FLAGS ?=
MATRIX ?= 8.2:7.4 8.3:7.4 8.4:7.4 8.4:8.1 8.5:8.1

export PHP_VERSION
export USER_ID := $(shell id -u)
export GROUP_ID := $(shell id -g)
export SYMFONY_REQUIRE := $(if $(SYMFONY),$(SYMFONY).*,)

DOCKER_COMPOSE ?= docker compose -f .devcontainer/docker-compose.yml
# Only allocate a TTY when there is one (keeps it working in CI and scripts).
TTY := $(shell test -t 0 || echo -T)
RUN := $(DOCKER_COMPOSE) run --rm $(TTY) app

.DEFAULT_GOAL := help
.PHONY: help build shell php composer install update validate tests test coverage lint fix ci matrix lowest clean

help: ## Show this help
	@grep -hE '^##' $(MAKEFILE_LIST) | sed -e 's/^## \{0,1\}//'
	@echo
	@grep -hE '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

build: ## Build the dev container image for PHP_VERSION
	$(DOCKER_COMPOSE) build app

shell: ## Open a shell in the dev container
	$(RUN) bash

php: ## Run a PHP command, e.g. make php c="-v"
	$(RUN) php $(c)

composer: ## Run a composer command, e.g. make composer c="require foo/bar" (default: install)
	$(RUN) composer $(or $(c),install)

install: ## Install dependencies
	$(RUN) composer install --no-interaction --no-progress $(COMPOSER_FLAGS)

update: ## Update dependencies (honours SYMFONY=x.y and COMPOSER_FLAGS)
	$(RUN) composer update --no-interaction --no-progress $(COMPOSER_FLAGS)

validate: ## Validate composer.json
	$(RUN) composer validate --strict

vendor/autoload.php:
	$(MAKE) --no-print-directory install

tests: vendor/autoload.php ## Run the PHPUnit test suite
	$(RUN) vendor/bin/phpunit $(c)

test: tests

coverage: vendor/autoload.php ## Run the test suite with HTML coverage report in var/coverage
	$(DOCKER_COMPOSE) run --rm $(TTY) -e XDEBUG_MODE=coverage app vendor/bin/phpunit --coverage-html var/coverage --coverage-text

lint: vendor/autoload.php ## Check coding standards (php-cs-fixer dry run)
	$(RUN) vendor/bin/php-cs-fixer fix --dry-run --diff --verbose

fix: vendor/autoload.php ## Fix coding standards
	$(RUN) vendor/bin/php-cs-fixer fix --verbose

ci: validate update tests ## Run what GitHub Actions runs, for the current PHP_VERSION / SYMFONY

matrix: ## Update + test every PHP/Symfony pair of MATRIX (rewrites vendor/)
	@set -e; for pair in $(MATRIX); do \
		php=$${pair%%:*}; sf=$${pair#*:}; \
		printf '\n==> PHP %s / Symfony %s\n' "$$php" "$$sf"; \
		$(MAKE) --no-print-directory update tests PHP_VERSION=$$php SYMFONY=$$sf; \
	done

# --no-blocking: the lowest versions are EOL and have security advisories, fine for testing only
lowest: ## Test with the lowest supported dependencies (PHP 8.2 / Symfony 7.0)
	$(MAKE) --no-print-directory update tests PHP_VERSION=8.2 SYMFONY=7.0 COMPOSER_FLAGS="--prefer-lowest --no-blocking"

clean: ## Remove vendor/, composer.lock and caches
	rm -rf vendor composer.lock .phpunit.cache .php-cs-fixer.cache var
