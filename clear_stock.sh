#!/bin/bash
cd /usr/local/nginx/stock

chmod -R 777 ./xhprof
chmod -R 777 ./cache
chmod -R 777 ./logs

find ./logs -mtime +7 -type f | grep -v ".gitignore" | xargs --no-run-if-empty rm -vrf

find ./cache -mtime +7 -type f | grep -v ".gitignore" | grep -v ".htaccess" | xargs --no-run-if-empty rm -vrf

find ./logs -type d -empty | xargs --no-run-if-empty rmdir -v
