phpcs:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src

phpstan:
	@APP_ENV=test bin/console cache:warmup
	vendor/bin/phpstan clear-result-cache
	php vendor/bin/phpstan analyse --memory-limit=-1
