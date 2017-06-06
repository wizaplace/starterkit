
all: install

install: composer-install npm-install assets

install-ci: composer-install-ci npm-install assets

composer-install:
	composer install

composer-install-ci:
	composer install --no-interaction --no-progress --ignore-platform-reqs

lint: lint-php lint-twig lint-yaml lint-xliff

lint-ci: lint-php-ci lint-twig lint-yaml lint-xliff

lint-php:
	./vendor/bin/coke

lint-php-ci:
	./vendor/bin/coke --report-checkstyle=coke-checkstyle.xml --report-full

lint-twig:
	bin/console lint:twig app src

lint-yaml:
	bin/console lint:yaml app
	bin/console lint:yaml src

lint-xliff:
	bin/console lint:xliff app
	bin/console lint:xliff src

stan:
	./vendor/bin/phpstan analyse -l 4 src tests

stan-ci:
	./vendor/bin/phpstan --no-interaction analyse -l 4 src tests

test:
	./vendor/bin/phpunit --configuration ./phpunit.xml

test-ci:
	chmod -R 777 ./var/logs
	php -dxdebug.coverage_enable=1 ./vendor/bin/phpunit --configuration ./phpunit.xml --log-junit ./phpunit-result.xml --coverage-clover ./clover.xml

npm-install:
	npm install

assets:
	gulp deploy

dev-from-scratch:
	vagrant destroy -f && vagrant up

.PHONY: all install install-ci composer-install composer-install-ci npm-install assets lint lint-ci lint-php lint-php-ci lint-yaml lint-twig lint-xliff stan stan-ci test test-ci dev-from-scratch
