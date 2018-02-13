#!/usr/bin/env bash

cd /app/var/logs && gzip -c prod.json > prod.json.$(date "+%Y-%m-%d_%H-%M-%S").gz && echo -n > prod.json && find . -type f -name "*.gz" -mtime +7 -delete
