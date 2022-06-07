start:
	@cd docker/dev \
	&& docker-compose up -d

stop:
	@cd docker/dev \
	&& docker-compose stop

#############
# Containers
#############

nginx:
	@cd docker/dev \
	&& docker-compose exec nginx bash

php:
	@cd docker/dev \
	&& docker-compose exec php bash

mysql:
	@cd docker/dev \
	&& docker-compose exec mysql bash

#############
# Tools
#############

#### External (outside the container)

phpstan:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpstan_command'

phpunit:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make create_test_db_command' \
	&& docker-compose exec php bash -c 'make phpunit_command'

phpcs:
	@cd docker/dev \
	&& docker-compose exec php bash -c 'make phpcs_command'

security:
	@cd docker/dev \
    && docker-compose exec php bash -c '/usr/local/bin/local-php-security-checker'

#### Internal (inside the container)

phpstan_command:
	@APP_ENV=test bin/console cache:warmup
	vendor/bin/phpstan clear-result-cache
	php vendor/bin/phpstan analyse --memory-limit=-1

phpunit_command:
	make create_test_db_command
	APP_ENV=test bin/console cache:warmup && vendor/bin/phpunit --testdox

phpcs_command:
	tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src
