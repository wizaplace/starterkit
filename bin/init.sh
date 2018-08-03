#!/bin/bash

set -e
rm -rf var/cache/prod
mkdir -p var/cache
mkdir -p var/logs
mkdir -p var/cache
mkdir -p var/sessions
mkdir -p var/sitemap
mkdir -p var/translations
bin/console cache:warmup
bin/console wizaplace:translations:push
