#!/bin/bash

set -e
bin/console --env=prod cache:warmup
bin/console --env=prod wizaplace:translations:push
