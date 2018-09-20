#!/usr/bin/env bash

set -e

cd /app/var/logs

if [ -f prod.json ]; then
    gzip -c prod.json > prod.json.$(date "+%Y-%m-%d_%H-%M-%S").gz
    echo -n > prod.json
fi

find . -type f -name "*.gz" -mtime +7 -delete
