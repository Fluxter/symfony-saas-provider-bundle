fix: ##@SaasProviderBundle Fix with PHP CS Fixer
	vendor/bin/php-cs-fixer fix --verbose --config .php_cs.dist

lint: ##@SaasProviderBundle Lint with PHP CS Fixer (dry-run)
	vendor/bin/php-cs-fixer fix  --verbose --config .php_cs.dist --dry-run --diff --diff-format udiff src/

test: ##@SaasProviderBundle Test with PHPUnit
	vendor/bin/phpunit