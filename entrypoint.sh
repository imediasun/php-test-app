#!/bin/sh

# Запустите миграции
vendor/bin/phinx migrate

# Запустите php-fpm
php-fpm --nodaemonize
