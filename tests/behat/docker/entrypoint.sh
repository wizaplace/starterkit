#!/usr/bin/env bash
set -e
set -x

/usr/bin/google-chrome --disable-gpu --headless --remote-debugging-address=0.0.0.0 --remote-debugging-port=9222 --no-sandbox &

bin/console --env=test server:start 127.0.0.1:8080

export TEST_WEBSERVER_URL=http://127.0.0.1/app_test.php/

php -d opcache.enable=0 vendor/bin/behat --config behat.yml --format=pretty --out=std --format=junit --out=behat-result
