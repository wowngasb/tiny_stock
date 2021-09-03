#!/bin/bash
git pull
php cmd.php clear
php cachetool.phar opcache:reset --fcgi=127.0.0.1:9000