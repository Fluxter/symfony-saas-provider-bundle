ARG PHP_VERSION=8.4

FROM composer:2 AS composer

FROM php:${PHP_VERSION}-cli

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && { \
        echo 'xdebug.client_host=host.docker.internal'; \
        echo 'xdebug.client_port=9003'; \
        echo 'xdebug.start_with_request=yes'; \
    } > /usr/local/etc/php/conf.d/zz-xdebug.ini \
    && echo 'memory_limit=-1' > /usr/local/etc/php/conf.d/zz-memory.ini \
    && git config --system --add safe.directory /workspace

COPY --from=composer /usr/bin/composer /usr/bin/composer

# Run as the host user, so files written to the mounted workspace (vendor/, caches) are owned by them.
RUN (getent group "${GROUP_ID}" || groupadd --gid "${GROUP_ID}" dev) \
    && useradd --uid "${USER_ID}" --gid "${GROUP_ID}" --create-home --shell /bin/bash dev \
    && mkdir -p /composer/cache /workspace \
    && chown -R "${USER_ID}:${GROUP_ID}" /composer /workspace

ENV COMPOSER_HOME=/composer \
    COMPOSER_CACHE_DIR=/composer/cache \
    XDEBUG_MODE=off \
    PATH="/composer/vendor/bin:${PATH}"

USER dev

# symfony/flex honours SYMFONY_REQUIRE (e.g. "7.4.*") on composer update, which pins all symfony/* packages.
RUN composer global config --no-plugins allow-plugins.symfony/flex true \
    && composer global require --no-progress --no-interaction symfony/flex

WORKDIR /workspace
