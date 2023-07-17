FROM php:8.1-fpm

RUN apt-get update && \
apt-get install -y curl zlib1g-dev

# Install pecl-memcache
RUN curl -L -o /tmp/memcache.tar.gz "https://github.com/websupport-sk/pecl-memcache/archive/refs/tags/8.2.tar.gz" \
    && mkdir -p /usr/src/php/ext/memcache \
    && tar -C /usr/src/php/ext/memcache -zxvf /tmp/memcache.tar.gz --strip 1 \
    && docker-php-ext-install memcache \
    && rm /tmp/memcache.tar.gz
