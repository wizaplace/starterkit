FROM node:6 as assets

# assets
WORKDIR /app
COPY package.json package-lock.json /app/
RUN npm install --no-save
COPY . /app
RUN node_modules/.bin/gulp deploy

# vendors
FROM composer as vendors
COPY composer.json composer.lock /app/
RUN composer global require hirak/prestissimo \
    && composer install --ignore-platform-reqs --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader --no-scripts
COPY . /app
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-interaction

# Production image
FROM php:7.1-apache as app
LABEL maintainer="dev@wizaplace.com"

COPY . /var/www/html/
# Copie des vendors installé dans l'image temporaire qui contient composer
COPY --from=vendors /app/vendor/ /var/www/html/vendor
# Copie des assets générés dans l'image temporaire node
COPY --from=assets /app/web/ /var/www/html/web
RUN chown -R www-data:www-data /var/www/html/var

ENV SYMFONY_ENV "prod"

RUN touch app/config/parameters.yml \
    && bin/console --env=prod assets:install \
    && bin/console --env=prod fos:js-routing:dump
