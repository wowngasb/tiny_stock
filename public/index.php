<?php

use app\App;
use app\Boot;
use app\Controller;
use app\Request;
use app\Response;
use app\Util;
use Tiny\Plugin\ThrottleHelper;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = Boot::bootstrap(
    App::app('app', require(dirname(__DIR__) . "/config/app-config.ignore.php"))
);
$request = Request::createFromGlobals();

if ($app::config('services.throttle.enable', false) && ThrottleHelper::checkPassIpThrottle($app, $request->client_ip(), $request->getRequestUri()) <= 0) {
    $ip = $request->client_ip();
    if ((crc32($ip) + time()) % 60 == 1) {
        $agent = $request->_server('HTTP_USER_AGENT', '');
        $header = json_encode(Util::lower_key($request->request_header()));
        $device = Util::device_type($agent);
        error_log("SKIP ip:{$ip}, device:{$device}, agent:{$agent} header:{$header}");
    }
    http_response_code(444);
    exit('error');
} else {
    $domain = $request->host();
    $path = $request->path();
    $is_pre = false;

    if ($domain == 'pre-test.wsdev.com') {
        $is_pre = true;
    } else {
        foreach (Controller::PRE_PATH_LIST as $pre) {
            if (Util::stri_startwith($path, $pre)) {
                $tmp = '';
                $tmp = !empty($tmp) ? trim($tmp) : '';
                $is_pre = $tmp == 'pre';
                break;
            }
        }
    }

    if ($is_pre) {
        Boot::_useConfigWithTest($app);
    }
    $app->run($request, new Response());
}
