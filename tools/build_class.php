<?php

use app\api\ApiHub;
use app\App;
use app\Boot;
use app\Util;
use Tiny\Application;
use Tiny\Plugin\DbHelper;
use Tiny\Plugin\develop\controller\deploy;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = Boot::bootstrap(
    App::app('app', require(dirname(__DIR__) . "/config/app-config.ignore.php"))
);

DbHelper::setOrmEventCallback(function ($type, $event) {
    if ($type == 'QueryExecuted') {
        $sql_str = Util::prepare_query($event->sql, $event->bindings);
        echo "{$event->time}ms => {$sql_str} \n";
    }
});

$dev_debug = App::dev() ? 1 : 0;

$ret = deploy::_buildApiModJs($dev_debug);
var_dump($ret);

$ret = deploy::_buildApiModJsForYc($dev_debug);
var_dump($ret);

$introduction_path = App::path(['public', 'doc', 'api_static', '介绍(Introduction).md'], false);
$introduction_path = iconv('UTF-8', 'GBK', $introduction_path);
$apimodel_path = App::path(['public', 'doc', 'api_static', '常用模型(ApiModel).md'], false);
$apimodel_path = iconv('UTF-8', 'GBK', $apimodel_path);
list($_apiModel, $defMap) = deploy::_buildDefineDoc('ApiModel', file_get_contents($apimodel_path), []);
$preDocs = [
    'Introduction' => file_get_contents($introduction_path),
    'ApiModel' => $_apiModel,
];

$message_path = Application::path(['public', 'doc', 'api_static', '消息格式(Message).md'], false);
$message_path = iconv('UTF-8', 'GBK', $message_path);
list($_message, $defMap) = deploy::_buildDefineDoc('Message', file_get_contents($message_path), $defMap);
$lastDocs = [
    'Message' => $_message,
];
$clsMap = [
    'ApiHub' => [
    ],
];

try {
    $ret = deploy::_buildApiDoc('api', $clsMap, $preDocs, $lastDocs, $defMap);
    var_dump($ret);
} catch (Exception $e) {
    echo "_buildApiDoc with Exception =>\n #Msg: {$e->getMessage()}\n #Trace:\n";
    echo $e->getTraceAsString();
}


$ret = ApiHub::_autoBuildDartAppApi();
var_dump($ret);
