FROM php:8.1-fpm

ARG XDEBUG_INSTALL
ARG USER_DOCKER_ID
ARG GROUP_DOCKER_ID
ARG PROJECT_PATH

# Redis
RUN pecl install redis
RUN echo "extension=redis.so" >> /usr/local/etc/php/conf.d/docker-php-redis.ini

RUN apt-get update -y && apt-get upgrade -y && apt-get install -y \
    apt-utils \
    iputils-ping \
    git \
    wget \
    sendmail \
    libpng-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libpng-dev libxpm-dev \
    libfreetype6-dev \
    libmagickwand-dev \
    curl \
    gnupg \
    zlib1g-dev \
    libzip-dev \
    libxml2-dev \
    libonig-dev \
    libpq-dev \
    libc-client-dev \
    libkrb5-dev \
    zip \
    unzip \
    cron \
    build-essential \
    tar \
    make \
    tsung

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

ADD ./php.ini /usr/local/etc/php/php.ini

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install imagick \
    && docker-php-ext-enable imagick

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install pdo_mysql intl mbstring zip gd soap exif bcmath imap pcntl posix

RUN pecl install apcu-5.1.21
RUN docker-php-ext-enable apcu opcache

# Memory Limit
RUN cd /usr/local/etc/php/conf.d/ && \
  echo 'memory_limit = -1' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y nodejs \
        npm
RUN echo "NODE Version:" && node --version
RUN echo "NPM Version:" && npm --version

# Installing Yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && apt-get install -y yarn

RUN mkdir -p ${PROJECT_PATH}

RUN mkdir -p /var/www/.config/psysh
RUN chmod -R 755 /var/www/.config

RUN mkdir -p /var/www/.composer
RUN chmod -R 755 /var/www/.composer
RUN chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} /var/www/.composer

RUN mkdir -p /var/www/.npm
RUN chmod -R 755 /var/www/.npm
RUN chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} /var/www/.npm

#RUN usermod -u ${USER_DOCKER_ID} www-data && groupmod -g ${GROUP_DOCKER_ID} www-data
RUN chown -R www-data:www-data ${PROJECT_PATH}
RUN chmod -R 755 ${PROJECT_PATH}
RUN chown -R ${USER_DOCKER_ID}:${GROUP_DOCKER_ID} /var/www/.config

WORKDIR ${PROJECT_PATH}
USER "${USER_DOCKER_ID}:${GROUP_DOCKER_ID}"
