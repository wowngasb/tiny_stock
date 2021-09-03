#!/bin/bash
stock_proce=`ps -ef | grep 'stock_script.php' | grep -v grep | wc -l`

if [ $stock_proce -lt 1 ];then
    cd /usr/local/nginx/stock && sh run_stock.sh
    echo "`date  +%F' '%R` run_stock   CHECK"   >> /tmp/check_stock.log
fi