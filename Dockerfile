FROM php:8.2-fpm

# Установка необходимых библиотек и расширений
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    git \
    zip \
    unzip \
    inetutils-ping \
    telnet \
    && docker-php-ext-install pdo pdo_mysql

RUN DEBIAN_FRONTEND=noninteractive apt-get install -y \
        python3 \
        && ( \
            cd /tmp \
            && mkdir librdkafka \
            && cd librdkafka \
            && git clone https://github.com/edenhill/librdkafka.git . \
            && ./configure \
            && make \
            && make install \
        ) \
    && rm -r /var/lib/apt/lists/*

RUN pecl install rdkafka \
    && docker-php-ext-enable rdkafka

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html

# Установка зависимостей проекта
COPY ./public .
RUN chmod 777 /var/www/html

CMD ["php-fpm", "--nodaemonize"]
