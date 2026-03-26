FROM php:8.1.10-fpm
LABEL Maintainer = "Matias Cappato <mcappato@fromdoppler.com>"

COPY ./php.conf.ini /usr/local/etc/php/conf.d/conf.ini

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y libmemcached-dev zlib1g-dev \
    && pecl install memcached-3.1.4 \
    && docker-php-ext-enable memcached

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

RUN \
    docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
    && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
    && docker-php-ext-install pdo_mysql

RUN apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    software-properties-common \
    && apt-get update \
    && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libfreetype6-dev \
    libicu-dev \
    libssl-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libedit-dev \
    libedit2 \
    libpq-dev \
    libxslt1-dev \
    libzip-dev \
    apt-utils \
    gnupg \
    git \
    vim \
    wget \
    curl \
    lynx \
    psmisc \
    unzip \
    tar \
    cron \
    bash-completion

#Install Dependencies
RUN docker-php-ext-configure \
    gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/; \
    docker-php-ext-install \
    opcache \
    gd \
    bcmath \
    intl \
    soap \
    xsl \
    zip

RUN apt-get update && \
    \
    apt-get install -y --no-install-recommends \
    libldap2-dev \
    libldb-dev \
    libmcrypt-dev \
    libsodium-dev \
    zlib1g-dev

RUN chmod 777 -R /var/www \
    && chown -R www-data:www-data /var/www \
    && usermod -u 1000 www-data \
    && chsh -s /bin/bash www-data


RUN docker-php-ext-install sockets
#RUN docker-php-ext-install sodium
