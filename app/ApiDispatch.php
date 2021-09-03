<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2019/5/7 0007
 * Time: 14:49
 */

namespace app;


use Exception;
use Tiny\Abstracts\AbstractApi;
use Tiny\Abstracts\AbstractContext;
use Tiny\Dispatch\ApiDispatch as ApiDispatchAlias;
use Tiny\Interfaces\ResponseInterface;
use Tiny\Traits\LogTrait;

class ApiDispatch extends ApiDispatchAlias
{

    /**
     * 处理异常接口 用于捕获分发过程中的异常
     * @param AbstractContext $context
     * @param $action
     * @param $params
     * @param Exception $ex
     * @param bool $get_previous
     * @throws Exception
     */
    public static function traceExceptionWithLog(AbstractContext $context, $action, $params, Exception $ex, $get_previous = true)
    {
        $log_msg = __METHOD__ . " ex:" . $ex->getMessage() . " <" . get_class($ex) . ">";
        error_log($log_msg);

        $request = $context->getRequest();
        $response = $context->getResponse();
        $response->resetBody();

        $result = static::_buildExceptionResult($ex, $get_previous);

        $callback = $request->_get('callback');
        $result_str = json_encode($result);
        $json_str = !empty($callback) ? "{$callback}({$result_str});" : $result_str;
        $response->addHeader('Content-Type: application/json;charset=utf-8', false)->appendBody($json_str);

        $ret_length = strlen($json_str);
        $use_time = $request->usedMilliSecond();
        $class = get_class($context);
        $error_class = get_class($ex);
        $error_msg = $ex->getMessage();
        $ret_code = Util::v($result, 'code', 500);
        $ret_code = $ret_code >= 400 && $ret_code <= 999 ? intval($ret_code) : 500;

        $headers = $request->lower_header();
        $ctrl = Controller::_getRequestByCtx();
        $ip = !empty($ctrl) ? $ctrl->client_ip() : '0.0.0.0';
        $op_uid = Controller::_getAuthByCtx() ? Controller::_getAuthByCtx()->id() : 0;

        $data = [
            'api_class' => $class,
            'action' => $action,
            'ret_length' => $ret_length,
            'ret_used' => $use_time,
            'ret_code' => $ret_code,
            'op_uid' => $op_uid,
            'op_ip' => strlen($ip) > 32 ? substr($ip, 0, 32) : $ip,
            'op_location' => Util::getIpLocation($ip),
            'error_class' => $error_class,
            'error_msg' => $error_msg,

            'serial_number' => Util::v($headers, 'serial-number', ''),
            'net_type' => Util::v($headers, 'net-type', ''),
            'prom_channel' => Util::v($headers, 'prom-channel', ''),
            'user_token' => Util::v($headers, 'user-token', ''),
            'app_ver' => Util::v($headers, 'app-ver', ''),
            'accept_language' => Util::v($headers, 'accept-language', ''),

            'params_str' => json_encode($params),
        ];
        /*
        'api_class',    //  VARCHAR(128)  请求类
        'action',    //  VARCHAR(128)  请求方法
        'ret_length',    //  INTEGER  结果长度
        'ret_used',    //  INTEGER  响应时间 单位毫秒
        'ret_code',    //  VARCHAR(16)  返回状态码
        'op_uid',    //  INTEGER  操作者  uid
        'op_ip',    //  VARCHAR(32)  操作 IP
        'op_location',    //  VARCHAR(32)  操作 地域
        'error_class',    //  VARCHAR(32)  出错类
        'error_msg',    //  VARCHAR(256)  出错信息

        'serial_number',    //  VARCHAR(64)  设备序列号
        'net_type',    //  VARCHAR(64)  网络类型
        'prom_channel',    //  VARCHAR(64)  推广渠道
        'app_token',    //  VARCHAR(64)  app token
        'app_ver',    //  VARCHAR(64)  app ver

        'params_str',    //  TEXT  参数 json
         * */

        // $id = SiteApiRecord::createOne($data);

        if (App::dev() || App::config('app.error_log_api', false) || $result_str == $data) {
            $params_str = json_encode($params);
            $result_str = LogTrait::_mixed2msg($result, 10, 90);
            unset($headers['user-agent'], $headers['cookie'], $headers['accept-encoding'], $headers['content-type'], $headers['content-length'], $headers['connection'], $headers['x-forwarded-for'], $headers['host'], $headers['remoteip']);
            $header_str = json_encode($headers);
            $log_msg = "{$class}::{$action}<{$ret_code}#{$ret_length}>@{$ip} {$use_time}ms uid:{$op_uid}, error:{$error_class}<$error_msg>, args:{$params_str}, header:{$header_str}, result:{$result_str}";
            self::error($log_msg, __METHOD__, __CLASS__, __LINE__);
        }
    }


    private static function _logApiResult(AbstractContext $context, $action, $params, $result, $ret_length)
    {
        if (!App::config('app.dev_log_api', false)) {
            return;
        }

        $request = $context->getRequest();
        $use_time = $request->usedMilliSecond();

        $class = get_class($context);
        $result = Util::try2array($result);
        $ret_code = Util::v($result, 'code', 0);
        $result_str = json_encode($result);

        $headers = $request->lower_header();
        $ctrl = Controller::_getRequestByCtx();
        $ip = !empty($ctrl) ? $ctrl->client_ip() : '0.0.0.0';
        $op_uid = Controller::_getAuthByCtx() ? Controller::_getAuthByCtx()->id() : 0;

        $data = [
            'api_class' => $class,
            'action' => $action,
            'ret_length' => $ret_length,
            'ret_used' => $use_time,
            'ret_code' => $ret_code,
            'op_uid' => $op_uid,
            'op_ip' => strlen($ip) > 32 ? substr($ip, 0, 32) : $ip,
            'op_location' => Util::getIpLocation($ip),
            'error_class' => '',
            'error_msg' => '',

            'serial_number' => Util::v($headers, 'serial-number', ''),
            'net_type' => Util::v($headers, 'net-type', ''),
            'prom_channel' => Util::v($headers, 'prom-channel', ''),
            'user_token' => Util::v($headers, 'user-token', ''),
            'app_ver' => Util::v($headers, 'app-ver', ''),
            'accept_language' => Util::v($headers, 'accept-language', ''),

            'params_str' => json_encode($params),
        ];
        /*
        'api_class',    //  VARCHAR(128)  请求类
        'action',    //  VARCHAR(128)  请求方法
        'ret_length',    //  INTEGER  结果长度
        'ret_used',    //  INTEGER  响应时间 单位毫秒
        'ret_code',    //  VARCHAR(16)  返回状态码
        'op_uid',    //  INTEGER  操作者  uid
        'op_ip',    //  VARCHAR(32)  操作 IP
        'op_location',    //  VARCHAR(32)  操作 地域
        'error_class',    //  VARCHAR(32)  出错类
        'error_msg',    //  VARCHAR(256)  出错信息

        'serial_number',    //  VARCHAR(64)  设备序列号
        'net_type',    //  VARCHAR(64)  网络类型
        'prom_channel',    //  VARCHAR(64)  推广渠道
        'app_token',    //  VARCHAR(64)  app token
        'app_ver',    //  VARCHAR(64)  app ver

        'params_str',    //  TEXT  参数 json
         * */

        // $id = SiteApiRecord::createOne($data);

        if (App::dev() || App::config('app.debug_log_api', false) || $result_str == $data) {
            $params_str = json_encode($params);
            $result_str = LogTrait::_mixed2msg($result, 3, 30);
            unset($headers['user-agent'], $headers['cookie'], $headers['accept-encoding'], $headers['content-type'], $headers['content-length'], $headers['connection'], $headers['x-forwarded-for'], $headers['host'], $headers['remoteip']);
            $header_str = json_encode($headers);
            $log_msg = "{$class}::{$action}<{$ret_code}#{$ret_length}>@{$ip} {$use_time}ms uid:{$op_uid}, args:{$params_str}, header:{$header_str}, result:{$result_str}";
            self::debug($log_msg, __METHOD__, __CLASS__, __LINE__);
        }
    }

    /**
     * @param AbstractContext $context
     * @param $action
     * @param array $params
     * @throws Exception
     */
    public static function dispatch(AbstractContext $context, $action, array $params)
    {
        $request = $context->getRequest();
        $response = $context->getResponse();

        $callback = $request->_get('callback');
        try {
            /** @var AbstractApi $context */
            $response->ob_start();
            $result = call_user_func_array([$context, $action], $params);
            $response->ob_get_clean();

            if ($result instanceof ResponseInterface) {
                return;
            }

            $result = Util::try2array($result);
            if (!isset($result['code'])) {
                $result['code'] = 0;
            }
            $context->_doneApi($action, $params, $result, $callback);

            $json_str = !empty($callback) ? "{$callback}(" . json_encode($result) . ');' : json_encode($result);

            $response->addHeader('Content-Type: application/json;charset=utf-8', false)->appendBody($json_str);

            self::_logApiResult($context, $action, $params, $result, strlen($json_str));

        } catch (Exception $ex2) {
            $context->_exceptApi($action, $params, $ex2, $callback);

            self::traceExceptionWithLog($context, $action, $params, $ex2);
        }
    }

}