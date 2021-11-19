# syntax=docker/dockerfile:1
FROM ubuntu:16.04

ENV DOCKERCONF=/root/dockerconf
COPY conf/cleanup.sh ${DOCKERCONF}/


ENV LIBICONV_FILE libiconv-1.16.tar.gz
ENV LIBICONV_URL http://ftp.gnu.org/pub/gnu/libiconv/libiconv-1.16.tar.gz 

ENV MYSQL_URL http://cdn.mysql.com/archives/mysql-5.5/mysql-5.5.62.tar.gz
ENV MYSQL_FILENAME mysql-5.5.62.tar.gz

ENV PHP_VERSION 5.5.9
ENV PHP_FILENAME php-5.5.9.tar.bz2
ENV PHP_URL http://phpsources.net/zip_/php-5.5.9.tar.bz2
ENV PHP_SHA256 9d1dea5195e2bcd928416130a6e19173d02bd36fb76c382522bf145c458fbed3

ENV HTTPD_VERSION 2.4.7
ENV HTTPD_BZ2_URL http://phpsources.net/zip_/httpd-2.4.7.tar.gz
ENV HTTPD_FILENAME httpd-2.4.7.tar.gz

ENV COMPOSER_URL https://raw.githubusercontent.com/composer/getcomposer.org/8dcf1c332251df61edee1be4ef8b3abb2b52d0b8/web/installer

ENV NODE_URL https://deb.nodesource.com/setup_4.x

ENV installPackages=" \
    gnupg \
    tzdata \
    libedit2 \
    libsqlite3-0 \
    libxml2 \
    libcurl3 "

ENV toolsPackages=" \    
    curl \
    net-tools \
    iputils-ping \
    nano "

ENV buildDeps=" \
    apt-utils \
    ca-certificates  \
    wget \
    libcurl4-openssl-dev \
    libc-dev \
    libaio-dev \
    libedit-dev \
    libsqlite3-dev \
    libbz2-dev \
    libjpeg-dev \
    libssl-dev \
    libxml2-dev \
    xz-utils \
    bzip2 \
    \
    libcurl4-openssl-dev \
    libedit-dev \
    libsqlite3-dev \
    libssl-dev \
    libxml2-dev \
    xz-utils \
    libncurses5-dev \
    libpng-dev  \
    libpng12-0  \
    libpng12-dev \
    \
    libapr1 \
    libaprutil1 \
    libaprutil1-ldap \
    libapr1-dev \
    libaprutil1-dev \
    libpcre++-dev \
    libssl1.0.0 \
    dbus \
    \
    gcc \
    g++ \
    libpcre++-dev \
    libssl-dev \
    make \
    perl \
    pwgen \
    wget \
    autoconf \
    file \
    re2c \
    pkg-config \
    cmake \
    libfreetype6-dev  \
    libfontconfig1-dev  \
    xclip   \
    libc6-dev \
    libzip-dev"


ENV DEBIAN_FRONTEND noninteractive

RUN set -xe \
    && apt-get update \
    && apt-get install -y $installPackages $toolsPackages --no-install-recommends \
    && apt-get install -y $buildDeps --no-install-recommends \
    && update-ca-certificates \
    && ${DOCKERCONF}/cleanup.sh

RUN ln -nfs /usr/share/zoneinfo/Europe/Paris /etc/localtime
ENV GOSU_VERSION 1.7

RUN set -xe \
    && wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$(dpkg --print-architecture)" \
    && chmod +x /usr/local/bin/gosu \
    && gosu nobody true \
    && ${DOCKERCONF}/cleanup.sh

RUN mkdir /docker-entrypoint-initdb.d
RUN touch ${DOCKERCONF}/exec.sh
RUN chmod ugo+x ${DOCKERCONF}/exec.sh

ENV MYSQL_MAJOR 5.5
ENV MYSQL_VERSION 5.5.62





RUN set -xe && groupadd -r mysql && useradd -r -g mysql mysql
COPY conf/my.cnf /etc/my.cnf
RUN set -xe \
    && cd /usr/src \  
    && mkdir mysql-src \
    && curl -kfSL "$MYSQL_URL" -o "$MYSQL_FILENAME" \
    && cd mysql-src \
    && tar -xf ../$MYSQL_FILENAME --strip-components=1 \
    && mkdir bld \
    && cd bld \
    && cmake .. -DBUILD_CONFIG=mysql_release \
    && make install \
    && cd /usr/local/mysql/ \
    && install -o mysql -d /var/run/mysqld \
    && install -o mysql -d /var/lib/mysql-files \
    && install -o mysql -d /var/lib/mysql \
    && /usr/local/mysql/scripts/mysql_install_db --user=mysql --basedir=/usr/local/mysql/ --datadir=/usr/local/mysql/data \
    && ${DOCKERCONF}/cleanup.sh

ENV PATH "/usr/local/mysql/bin:${PATH}"






ENV HTTPD_PREFIX /usr/local/apache2
ENV PATH "${HTTPD_PREFIX}/bin:$PATH"
RUN mkdir -p "$HTTPD_PREFIX" \
    && chown www-data:www-data "$HTTPD_PREFIX"\
    && cd /usr/src \  
    && curl -kfSL "$HTTPD_BZ2_URL" -o $HTTPD_FILENAME \
    && mkdir httpd-src \
    && cd httpd-src \
    && tar -xvf ../$HTTPD_FILENAME --strip-components=1 \
    && ./configure \
    --prefix="$HTTPD_PREFIX" \
    --enable-log_config \
    --enable-mime-magic \  
    --enable-log_config \
    --enable-mime \
    --enable-dir \
    --enable-authn_core \
    --enable-authz_core \
    --enable-unixd \
    --enable-env \
    --enable-headers \
    --enable-rewrite \
    --enable-mods-shared=reallyall \
    && make -j"$(nproc)" \
    && make install \
    && cd .. \
    && mkdir -p /var/log/apache2 \
    && ${DOCKERCONF}/cleanup.sh

COPY conf/httpd.conf "$HTTPD_PREFIX/conf/httpd.conf"
COPY conf/mime.types "$HTTPD_PREFIX/conf/mime.types"
RUN mkdir "/var/www"




ENV PHP_INI_DIR /usr/local/etc/php
RUN mkdir -p $PHP_INI_DIR/conf.d



RUN set -xe \
    && cd /usr/src \ 
    && wget -O $LIBICONV_FILE $LIBICONV_URL \
    && mkdir libiconv-src \
    && cd libiconv-src \
    && tar -xvf ../$LIBICONV_FILE --strip-components=1 \
    && ./configure --prefix=/usr/local/libiconv \
    && make -j"$(nproc)" \
    && make install \
    && ${DOCKERCONF}/cleanup.sh



RUN set -xe \
    && cd /usr/src \  
    && curl -kfSL "$PHP_URL" -o "$PHP_FILENAME" \
    && echo "$PHP_SHA256 $PHP_FILENAME" | sha256sum -c - \
    && mkdir php-src \
    && cd php-src \ 
    && tar -xf ../$PHP_FILENAME --strip-components=1  \
    && cd /usr/src/php-src \
    && ./configure \
    --with-mysql=/usr/local/mysql  \
    --with-mysqli=/usr/local/mysql/bin/mysql_config  \
    --with-mysql-sock=/var/lib/mysql/mysql.sock \
    --enable-sockets \
    --with-zlib \
    --with-zlib-dir \
    --with-bz2 \
    --enable-zip \
    --with-gd \
    --enable-gd \
    --enable-gd-native-ttf \
    --with-jpeg-dir=/usr/local \
    --with-png-dir=/usr/local \
    --with-ttf \
    --with-freetype-dir=/usr/local \
    --with-iconv=/usr/local/libiconv \
    --with-curl=/usr/local \
    --enable-track-vars \
    --with-gettext \
    --with-config-file-path=/usr/local/apache2/conf \
    --enable-trans-id \
    --enable-ftp \
    --with-config-file-path=/usr/local/apache2/conf \
    --enable-trans-id \
    --with-pdo-mysql \
    --with-apxs2=/usr/local/apache2/bin/apxs \
    --enable-mysqlnd \
    --enable-mbstring \
    --with-curl \
    --with-libedit \
    --with-openssl \
    --with-zlib \
    && make -j"$(nproc)" \
    && make install \
    && ${DOCKERCONF}/cleanup.sh


COPY docker-php-ext-* /usr/local/bin/

COPY conf/php.ini /usr/local/apache2/conf/php.ini

RUN set -xe \
    && cd ${HOME} \
    && wget $COMPOSER_URL -O - -q | php -- --quiet \
    && mv composer.phar /usr/local/bin/composer \
    && composer global require friendsofphp/php-cs-fixer \ 
    && composer clearcache \
    && ${DOCKERCONF}/cleanup.sh

ENV PATH "${HOME}/.composer/vendor/bin:${PATH}"

ENV NODE_PATH /usr/local/lib/node_modules
RUN set -xe \
    && mkdir $NODE_PATH \
    && curl -ksL $NODE_URL | bash \
    && apt-get --yes -qq install nodejs \
    && npm install --global --save-dev gulp-cli  \
    && npm install --global --save-dev gulp \
    && npm cache clean --force \
    && ${DOCKERCONF}/cleanup.sh


COPY conf/exec.sh ${DOCKERCONF}/
COPY conf/*.sql ${DOCKERCONF}/
COPY conf/pdo.php /var/www/gpci/pdo.php 

