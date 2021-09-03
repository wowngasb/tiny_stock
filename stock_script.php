<?php

use app\AbstractClass;
use app\App;
use app\Boot;
use app\Exception\NeverRunAtHereError;
use app\Exception\NotRetryTaskError;
use app\Util;
use Tiny\Traits\LogTrait;

require_once(__DIR__ . '/vendor/autoload.php');

Boot::bootstrap(
    App::app('app', require(__DIR__ . "/config/app-config.ignore.php"))
);

function main()
{
    $redis = AbstractClass::_getRedisInstance();
    $countly_pre = App::config('ENV_WEB.countly_pre', 'stock');
    $time_line_task = 'tiny_stock_tasks' . ":{$countly_pre}";
    while (true) {
        $data = $redis->blPop([$time_line_task], 1);

        if (empty($data)) {
            $now = time();
            sleep(1);
            $num = 0;
            if ($num == 0 && $now % 5 == 0) {
                echo '.';
                if ($num > 0 && $num % 100 == 0) {
                    echo "\n";
                }
            }
            continue;
        }
        $key = $data[0];
        $params = json_decode($data[1], true);

        $log_msg = "blPop key:{$key}, params:" . json_encode($params);
        echo "\n" . date('Y-m-d H:i:s') . " [INFO] " . $log_msg . "\n";
        LogTrait::debug($log_msg, 'main', 'main_script', __LINE__);

        try {
            if ($key == $time_line_task) {
                $action = Util::v($params, 'action');
                $track_id = Util::v($params, 'track_id');
                $track_id = $track_id > 0 ? intval($track_id) : 0;
                if ($track_id > 0 && $action == 'xxx') {
                    // do action
                } else {
                    throw new NeverRunAtHereError("error action or args for {$action}");
                }
            }
        } catch (\Exception $ex) {
            $retry = $ex instanceof NotRetryTaskError ? 0 : 1;
            $log_msg = "Exception key:{$key}, retry:{$retry}, params:" . json_encode($params) . ', ex:' . $ex->getMessage();
            echo "\n" . date('Y-m-d H:i:s') . " [ERROR] " . $log_msg . "\n";
            LogTrait::debug($log_msg, 'main', 'main_script', __LINE__);
            if (!empty($retry)) {
                $redis->rPush($key, json_encode($params));
            }
        }
    }

}

main();