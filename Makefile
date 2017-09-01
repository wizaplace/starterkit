
all: install

install: composer-install npm-install assets

install-ci: composer-install-ci npm-install assets

composer-install:
	composer install

composer-install-ci:
	composer install --no-interaction --no-progress --ignore-platform-reqs

lint: lint-php lint-twig lint-yaml lint-xliff lint-css

lint-ci: lint-php-ci lint-twig lint-yaml lint-xliff

lint-php:
	./vendor/bin/phpcs

lint-php-ci:
	./vendor/bin/phpcs --report-checkstyle=phpcs-checkstyle.xml --report-full

lint-twig:
	bin/console lint:twig app src

lint-yaml:
	bin/console lint:yaml app
	bin/console lint:yaml src

lint-xliff:
	bin/console lint:xliff app
	bin/console lint:xliff src

lint-css:
	gulp lint-css

stan:
	./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src tests

stan-ci:
	./vendor/bin/phpstan --no-interaction --no-progress analyse --errorFormat=checkstyle -c phpstan.neon -l 5 src tests > phpstan-checkstyle.xml || \
	(sed -i 's/<error/<error source="phpstan"/g' phpstan-checkstyle.xml && false)

test: test-phpunit test-behat

test-phpunit:
	./vendor/bin/phpunit --configuration ./phpunit.xml

test-phpunit-ci:
	chmod -R 777 ./var/logs
	php -dxdebug.coverage_enable=1 ./vendor/bin/phpunit --configuration ./phpunit.xml --log-junit ./phpunit-result.xml --coverage-clover ./clover.xml

test-behat:
	php -d opcache.enable=0 vendor/bin/behat --config behat.yml

test-behat-ci:
	php -d opcache.enable=0 vendor/bin/behat --config behat.yml --format=pretty --out=std --format=junit --out=behat-result

npm-install:
	npm install

assets:
	gulp deploy

dev-from-scratch:
	vagrant destroy -f && vagrant up

.PHONY: all install install-ci composer-install composer-install-ci npm-install assets lint lint-ci lint-php lint-php-ci lint-yaml lint-twig lint-xliff lint-css stan stan-ci test test-phpunit test-phpunit-ci test-behat test-behat-ci dev-from-scratch
