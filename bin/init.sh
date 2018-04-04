#!/bin/bash

set -e
rm -rf var/cache/prod
bin/console cache:warmup
bin/console wizaplace:translations:push
