#!/bin/bash
ps -ef | grep 'stock_script.php' | grep -v grep | awk '{print "kill -9",$2}' | sh
sleep 2
cd /usr/local/nginx/stock && sh run_stock.sh
echo "`date  +%F' '%R` run_stock   RESTART"   >> /tmp/restart_stock.log