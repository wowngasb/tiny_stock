# stock 后端项目

定时任务
``` bash
*  *  *  *  *  php  /usr/local/nginx/stock/public/helper/crontab.php >> /tmp/stock_crontab.log 2>&1

*  *  *  *  *  sh /usr/local/nginx/stock/check_stock.sh >> /tmp/stock_check.log 2>&1

5  5  *  *  *  sh /usr/local/nginx/stock/restart_stock.sh >> /tmp/stock_restart.log 2>&1

*  *  *  *  *  sh /usr/local/nginx/stock/clear_stock.sh >> /tmp/stock_clear.log 2>&1
```