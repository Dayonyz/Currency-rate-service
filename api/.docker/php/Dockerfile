FROM php:8.2-fpm

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
    procps

# Setup clear
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

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
        && docker-php-ext-enable redis

RUN pecl install apcu \
    && docker-php-ext-enable apcu opcache

#Make User and Group
RUN groupmod -o -g ${GROUP_DOCKER_ID} www-data && \
    usermod -o -u ${USER_DOCKER_ID} -g www-data www-data

# Npm+node.js
#RUN curl -sL https://deb.nodesource.com/setup_18.x | bash && \
#    apt-get install -y nodejs && \
#    mkdir -p /.npm && \
#    chmod -R 755 /.npm && \
#    chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} /.npm

# HOME for psysh tinker
#ENV HOME=/var/www

# Access for tinker
#RUN mkdir -p /var/www/.config && \
#    chmod -R 775 /var/www/.config && \
#    chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} /var/www/.config

# Composer
ENV COMPOSER_HOME=/var/www/.composer
ENV COMPOSER_CACHE_DIR=/var/www/.composer/cache
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN echo "$PATH"
ENV PATH="$PATH:/var/www/.composer/vendor/bin"
RUN mkdir -p $COMPOSER_CACHE_DIR && chmod -R 777 $COMPOSER_CACHE_DIR

# Project access
RUN mkdir -p ${PROJECT_PATH} && \
    chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} ${PROJECT_PATH} && \
    chmod -R 755 ${PROJECT_PATH}

WORKDIR ${PROJECT_PATH}

USER "${USER_DOCKER_ID}:${GROUP_DOCKER_ID}"