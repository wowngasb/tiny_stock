<?php

use app\App;
use app\Boot;
use app\Model\StockBase;
use app\Util;
use app\StockUtil;
use Tiny\Plugin\DbHelper;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = Boot::bootstrap(
    App::app('app', require(dirname(__DIR__) . "/config/app-config.ignore.php"))
);
function _t($t = 'INFO')
{
    return date('Y-m-d H:i:s') . " [{$t}] ";
}

DbHelper::setOrmEventCallback(function ($type, $event) {
    if ($type == 'QueryExecuted') {
        $sql_str = Util::prepare_query($event->sql, $event->bindings);
        echo "{$event->time}ms => {$sql_str}; \n";
    }
});

$is_test = false;
if ($is_test) {
    Boot::_useConfigWithTest($app);
}

$as_import = true;
if ($as_import) {
    DbHelper::setOrmEventCallback();
}
$mini_m = 202109;
$minline_start = 20210531;

$all_stock_file = 'D:\GitHub\mqttHub\database\data_fetch\all_stock.txt';
$tdx_dir = 'D:\Program Files (x86)\tdx\vipdoc';
$all_stock = StockUtil::build_all_stock($all_stock_file, $tdx_dir);

echo _t(), "======= START CHECK DAY ALL " . count($all_stock) . " ========\n";
StockUtil::_check_tdx_data_file($all_stock, $as_import);
echo "\n", _t(), "======= END CHECK DAY ALL " . count($all_stock) . " ========\n\n";


echo _t(), "======= START IMPORT DAY ALL " . count($all_stock) . " ========\n";
$buf = [];
$ldayMap = StockBase::getLdayMapGt($mini_m);
foreach ($all_stock as $c => $item) {
    if (empty($item['lday_ok'])) {
        continue;
    }
    $data = file_get_contents($item['lday']);
    $hasDays = !empty($ldayMap[$c]) ? $ldayMap[$c] : [];
    $mdata = StockUtil::load_lday_data($data, $mini_m, $hasDays);
    foreach ($mdata as $t => $mdatum) {
        if (in_array($t, $hasDays)) {
            continue;
        }
        $idata = StockUtil::build_stock_base($t, $c, $mdatum);
        $buf[] = $idata;
        if (count($buf) >= 100) {
            StockBase::tableBuilderEx()->insert($buf);
            $buf = [];
            if ($as_import) {
                echo '.';
            }
        }
    }
}
if (!empty($buf)) {
    StockBase::tableBuilderEx()->insert($buf);
}
echo "\n", _t(), "======= END IMPORT DAY ALL " . count($all_stock) . " ========\n\n";


echo _t(), "======= START IMPORT MINLINE ALL " . count($all_stock) . " ========\n";
$buf = [];
$idMap = StockBase::getIdMapGt($minline_start);
foreach ($all_stock as $c => $item) {
    if (empty($item['minline_ok'])) {
        continue;
    }
    $lastIds = !empty($idMap[$c]) ? $idMap[$c] : [];
    $needDays = array_map(function ($i) {
        return intval($i / 1000000);
    }, $lastIds);
    if (empty($needDays)) {
        continue;
    }

    $data = file_get_contents($item['minline']);
    $gdata = StockUtil::group_minline_data($data, $needDays);
    foreach ($lastIds as $id) {
        $d = intval($id / 1000000);
        if (empty($gdata[$d])) {
            continue;
        }
        $gz = gzcompress($gdata[$d], 9);
        $minline_data = StockUtil::safe_base64_encode($gz);

        $buf[] = ['id' => $id, 'minline_data' => $minline_data];
        if (count($buf) >= 20) {
            $ret = StockBase::updateBatch($buf);
            $buf = [];
            if ($as_import) {
                echo '.';
            }
        }
    }
}
if (!empty($buf)) {
    StockBase::updateBatch($buf);
}
echo "\n", _t(), "======= END IMPORT MINLINE ALL " . count($all_stock) . " ========\n\n";



