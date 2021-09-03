<?php

require_once(__DIR__ . '/vendor/autoload.php');

\app\Boot::bootstrap(
    \app\App::app('app', require(__DIR__ . "/config/app-config.ignore.php"))
);

echo \app\Util::cmd_do(!empty($argv[1]) ? trim($argv[1]) : 'unknown', $argv, function ($msg) {
        echo date('Y-m-d H:i:s') . " {$msg}\n";
    }) . "\n";