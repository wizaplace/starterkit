ENV ?= "dev"
JSON_FILE_SRC = $(shell find src/ -name '*.json')
JSON_FILE_APP = $(shell find app/ -name '*.json')

all: install

install: composer-install npm-install assets

install-ci: composer-install-ci npm-install assets

composer-install:
	composer install

composer-install-ci:
	composer install --no-interaction --no-progress --ignore-platform-reqs

lint: lint-php lint-twig lint-yaml lint-xliff lint-css

lint-ci: lint-php-ci lint-twig lint-yaml lint-xliff lint-json

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

lint-json:
	./vendor/bin/jsonlint $(JSON_FILE_SRC) $(JSON_FILE_APP)

stan:
	./vendor/bin/phpstan analyse -c phpstan.neon -l 5 app src

stan-ci:
	./vendor/bin/phpstan --no-interaction --no-progress analyse --errorFormat=checkstyle -c phpstan.neon -l 5 app src > phpstan-checkstyle.xml || \
	(sed -i 's/<error/<error source="phpstan"/g' phpstan-checkstyle.xml && false)

npm-install:
	npm install --no-save

assets:
	gulp deploy

dev-from-scratch:
	vagrant destroy -f && vagrant up

translations:
	rm -f var/translations/*.xliff
	rm -f var/cache/$(ENV)/translations/*
	bin/console --env=$(ENV) wizaplace:translations:push
	bin/console --env=$(ENV) wizaplace:translations:pull

docker-build:
	docker build -t wizaplace/starterkit .

.PHONY: all install install-ci composer-install composer-install-ci npm-install assets lint lint-ci lint-php lint-php-ci lint-yaml lint-twig lint-xliff lint-css stan stan-ci dev-from-scratch docker-build
