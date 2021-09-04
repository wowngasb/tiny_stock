<?php

use app\App;
use app\Boot;
use app\StockUtil;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = Boot::bootstrap(
    App::app('app', require(dirname(__DIR__) . "/config/app-config.ignore.php"))
);
function _t($t = 'INFO')
{
    return date('Y-m-d H:i:s') . " [{$t}] ";
}

$as_import = true;

$mini_m = 202109;
$minline_start = 20210531;

$all_stock_file = 'D:\GitHub\mqttHub\database\data_fetch\all_stock.txt';
$tdx_dir = 'D:\Program Files (x86)\tdx\vipdoc';
$all_stock = StockUtil::build_all_stock($all_stock_file, $tdx_dir);

echo _t(), "======= START CHECK DAY ALL " . count($all_stock) . " ========\n";
StockUtil::_check_tdx_data_file($all_stock, $as_import);
echo "\n", _t(), "======= END CHECK DAY ALL " . count($all_stock) . " ========\n\n";

