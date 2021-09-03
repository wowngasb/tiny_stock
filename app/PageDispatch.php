<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/29 0029
 * Time: 17:11
 */

namespace app;


use Exception;
use Tiny\Abstracts\AbstractContext;
use Tiny\Abstracts\AbstractDispatch;
use Tiny\Interfaces\RequestInterface;
use Tiny\Interfaces\ResponseInterface;

class PageDispatch extends AbstractDispatch
{

    /**
     * 根据对象和方法名 获取 修复后的参数
     * @param AbstractContext $context
     * @param $action
     * @param array $params
     * @return array
     */
    public static function initMethodParams(AbstractContext $context, $action, array $params)
    {
        return parent::initMethodParams($context, $action, $params);
    }

    /**
     * @param array $routeInfo
     * @return string
     */
    public static function initMethodName(array $routeInfo)
    {
        return parent::initMethodName($routeInfo);
    }

    /**
     * @param array $routeInfo
     * @return string
     */
    public static function initMethodNamespace(array $routeInfo)
    {
        $controller = !empty($routeInfo[1]) ? trim($routeInfo[1]) : 'index';
        $module = !empty($routeInfo[0]) ? trim($routeInfo[0]) : 'index';
        $appname = App::appname();
        return "\\" . Util::joinNotEmpty("\\", [$appname, $module, 'Controllers', $controller]);
    }

    /**
     * @param AbstractContext $context
     * @param string $action
     * @param array $params
     */
    public static function dispatch(AbstractContext $context, $action, array $params)
    {
        parent::dispatch($context, $action, $params);
    }


    private static $_trace_deep = 0;

    /**
     * 处理异常接口 用于捕获分发过程中的异常
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Exception $ex
     */
    public static function traceException(RequestInterface $request, ResponseInterface $response, Exception $ex)
    {
        if (self::$_trace_deep == 0) {
            self::$_trace_deep += 1;
            // 此时 需要保证 处理过程中 不再出现异常  否则会导致循环引用
            App::forward($request, $response, ['Http', 'ErrorController', 'page'], [
                'code' => $ex->getCode(),
                'ex' => $ex,
            ]);
        } else {
            App::traceException($request, $response, $ex);
        }
    }


    public static function traceNotFound(RequestInterface $request, ResponseInterface $response, Exception $ex)
    {
        if (self::$_trace_deep == 0) {
            self::$_trace_deep += 1;
            // 此时 需要保证 处理过程中 不再出现异常  否则会导致循环引用
            App::forward($request, $response, ['Http', 'ErrorController', 'page'], [
                'code' => 404,
                'ex' => $ex,
            ]);
        } else {
            App::traceNotFound($request, $response, $ex);
        }
    }

}