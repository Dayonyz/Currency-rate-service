FROM php:8.2-cli

ARG PROJECT_PATH
ARG USER_DOCKER_ID
ARG GROUP_DOCKER_ID

# OS packages
RUN apt-get update && apt-get install -y \
    curl \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    zip \
    unzip \
    cron \
    procps \
    libssl-dev \
    pkg-config \
    libpq-dev \
    libmemcached-dev \
    zlib1g-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-install \
        pdo_mysql \
        intl \
        mbstring \
        zip \
        soap \
        exif \
        bcmath \
        pcntl \
        posix \
        opcache \
    && pecl install redis \
    && pecl install swoole --configureoptions="--enable-brotli=no" \
    && pecl install memcached \
    && docker-php-ext-enable redis swoole memcached opcache

# User & Group
RUN groupmod -o -g ${GROUP_DOCKER_ID} www-data \
    && usermod -o -u ${USER_DOCKER_ID} -g www-data www-data

# Composer
ENV COMPOSER_HOME=/var/www/.composer
ENV COMPOSER_CACHE_DIR=/var/www/.composer/cache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV PATH="$PATH:/var/www/.composer/vendor/bin"
RUN mkdir -p $COMPOSER_CACHE_DIR && chmod -R 777 $COMPOSER_CACHE_DIR

# Project dir
RUN mkdir -p ${PROJECT_PATH} && \
    chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} ${PROJECT_PATH} && \
    chmod -R 755 ${PROJECT_PATH}

WORKDIR ${PROJECT_PATH}

USER "${USER_DOCKER_ID}:${GROUP_DOCKER_ID}"
