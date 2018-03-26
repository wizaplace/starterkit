#!/bin/bash

bin/console --env=prod cache:warmup
bin/console --env=prod wizaplace:translations:push
