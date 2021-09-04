<?php

namespace app;

use app\Libs\NewPaginationPresenter;
use Exception;
use FastRoute\RouteCollector;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\Paginator;
use PhpConsole\Connector;
use PhpConsole\Storage\File;
use Tiny\Abstracts\AbstractBoot;
use Tiny\Application;
use Tiny\Event\ApplicationEvent;
use Tiny\Event\CacheEvent;
use Tiny\Exception\AppStartUpError;
use Tiny\Interfaces\RequestInterface;
use Tiny\Plugin\DbHelper;
use Tiny\Plugin\develop\dispatch\DevelopDispatch;
use Tiny\Plugin\EmptyMock;
use Tiny\Plugin\graphiql\dispatch\GraphiQLDispatch;
use Tiny\Plugin\LogHelper;
use Tiny\Plugin\RedisSession;
use Tiny\Route\DefaultRoute;
use Tiny\Route\FastRoute;
use Tiny\Route\MapRoute;
use Tiny\Traits\CacheConfig;


final class Boot extends AbstractBoot
{

    private static $_request_debug = false;

    public static function _useConfigWithTest(Application $app)
    {
        $config = $app->getConfig();
        $config['ENV_DB'] = $config['ENV_DB_TEST'];
        $config['ENV_WEB']['countly_pre'] = $config['ENV_WEB']['countly_test'];
        $config['ENV_WEB']['name'] = $config['ENV_WEB']['name_test'];
        $app->setConfig($config);
    }

    public static function bootstrap(Application $app)
    {
        if ($app->isBootstrapCompleted()) {
            return $app;
        }
        # ApiDispatch 会保持 目录名 类名 原大小写状态 不作处理
        # 默认分发器 会强制把 所有目录转为小写
        $app->addRoute('r-api', new MapRoute('/api', 'api', ['api', 'ApiHub', 'hello']), new ApiDispatch())// 默认 API 路由
        ->addRoute('r-api-v100', new MapRoute('/apiv100', 'apiv100', ['apiv100', 'ApiHub', 'hello']), new ApiDispatch())// 默认 API 路由

        ->addRoute('r-develop', new MapRoute('/develop', 'develop', ['develop', 'index', 'index']), new DevelopDispatch())// 开发工具 插件
        ->addRoute('r-graphiql', new MapRoute('/graphiql', 'graphiql', ['graphiql', 'index', 'index']), new GraphiQLDispatch());// graphql 插件

        self::initCacheConfig();
        self::registerGlobalEvent();

        return parent::bootstrap($app);
    }

    public static function _tryGetUsedMilliSecond()
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            return $request->usedMilliSecond();
        }
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            return $request->usedMilliSecond();
        }
        return -1;
    }

    public static function getRouteCachePath()
    {
        return App::cache_path(['fast_route']);
    }

    /**
     * @return Connector
     * @throws Exception
     */
    protected static function getConsoleInstance()
    {
        //开启 辅助调试模式 注册对应事件
        if (empty(Boot::$_consoleInstance)) {
            if (!self::$_request_debug) {
                $console_path = self::getConsoleStorageFile();
                Connector::setPostponeStorage(new File($console_path));
            }

            Boot::$_consoleInstance = Connector::getInstance();
            if (!self::$_request_debug) {
                Boot::$_consoleInstance->setPassword(Application::config('ENV_DEVELOP_KEY'), true);
            }
        }
        return Boot::$_consoleInstance;
    }

    public static function getConsoleStorageFile()
    {
        return Application::cache_path(['console.data'], false);
    }

    private static function _webHostRoute()
    {
        return new FastRoute(function (RouteCollector $r) {
            $r->get('/', ['Http', 'Front\\IndexController', 'index']);
            $r->get('/index', ['Http', 'Front\\IndexController', 'index']);

            $r->get('/errorpage[/{code}]', ['Http', 'ErrorController', 'page']);

        }, ['Http', 'Front\\IndexController', 'index'], Util::path_join(self::getRouteCachePath(), ['route.r-fast-web.cache'], false), function (RequestInterface $request, $controller, $action) {
            return $action;
        });
    }

    private static function _dashboardHostRoute()
    {
        return new FastRoute(function (RouteCollector $r) {

            $r->addGroup('/admin', function (RouteCollector $r) {
                $r->post('/ajaxUpload', ['Http', 'UploadController', 'ajaxUpload']);
                $r->post('/csvDownload', ['Http', 'AdminDownloadController', 'csvDownload']);
            });

        }, ['Http', 'Front\\IndexController', 'index'], Util::path_join(self::getRouteCachePath(), ['route.r-fast-dashboard.cache'], false), function (RequestInterface $request, $controller, $action) {
            return $action;
        });
    }

    private static function _mgrHostRoute()
    {
        return new FastRoute(function (RouteCollector $r) {
            $r->addRoute(['GET', 'POST'], '/mgr/auth[/{_action}]', ['Http', "Front\\Auth\\MgrAuthController"]);

            $r->get('/mgr', ['Http', 'Mgr\\IndexController', 'index']);
            $r->addGroup('/mgr', function (RouteCollector $r) {
                $r->get('/', ['Http', "Mgr\\IndexController", "index"]);

                $r->post('/ajaxUpload', ['Http', 'UploadController', 'ajaxUpload']);
            });

        }, ['Http', 'Front\\IndexController', 'index'], Util::path_join(self::getRouteCachePath(), ['route.r-fast-mgr.cache'], false), function (RequestInterface $request, $controller, $action) {
            return $action;
        });
    }

    public static function _getSessionPreKey()
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'stock');
        return !empty($countly_pre) ? "_S_{$countly_pre}" : "_S_unknown";
    }

    public static function _getSessionMapKey()
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'stock');
        return !empty($countly_pre) ? "_U_{$countly_pre}" : "_U_unknown";
    }

    public static function tryStartSession(RequestInterface $request)
    {
        $countly_pre = App::config('ENV_WEB.countly_pre', 'stock');
        $session_name = "S_" . Util::short_md5($countly_pre);
        $request->session_name($session_name);
        $mRedis = AbstractClass::_getRedisInstance();
        if (empty($mRedis) || $mRedis instanceof EmptyMock) {
            error_log(__METHOD__ . ' can not session_set_save_handler RedisSession  mRedis by _getRedisInstance ');
        } else {
            $request->session_set_save_handler(new RedisSession(self::_getSessionPreKey(), 0, 3600 * 100), true);
        }

        $request->session_start();
        $sid = $request->session_id();
        if (empty($sid)) {
            $request->session_id(Util::rand_str(24));
        }
    }

    /**
     * @throws AppStartUpError
     */
    private static function registerGlobalEvent()
    {
        App::on('routerStartup', function (ApplicationEvent $event) {
            $request = $event->getRequest();
            $app = $event->getObject();

            $_environ = $request->_request('ENVIRON', $request->_cookie('ENVIRON'));
            if (!empty($_environ)) {
                if ($_environ == '_debug') {
                    $_environ = 'debug';
                    $request->setcookie('ENVIRON', $_environ, 0, '/');
                }
                if ($_environ == 'debug') {
                    self::$_request_debug = true;
                    self::$_consoleInstance = null;
                }

                $_environ = Util::trimlower($_environ);
                $_config = App::app()->getConfig();
                $_config['ENVIRON'] = $_environ;
                App::app()->setConfig($_config);
            }

            $app->setBootstrapCompleted(false);
            $app->addRoute('r-fast-web', self::_webHostRoute(), new PageDispatch());
            $app->addRoute('r-fast-mgr', self::_mgrHostRoute(), new PageDispatch());
            $app->addRoute('r-fast-dashboard', self::_dashboardHostRoute(), new PageDispatch());
            $app->addRoute('r-default', new DefaultRoute('/', ['Http', 'IndexController', 'index']), new PageDispatch());  // 添加默认简单路由 处理异常情况
            $app->setBootstrapCompleted();

            self::tryStartSession($request);

            if (App::dev()) {
                self::registerDebugEvent();
                self::actionRouterStartup($event);
            }
            return null;
        });

        // dispatchLoopStartup 分发开始  此时 response 才准备好 可以开启 session 把 sid 写入 cookie
        App::on('dispatchLoopStartup', function (ApplicationEvent $event) {
            $request = $event->getRequest();

            //   注册 分页类
            Paginator::currentPageResolver(function () use ($request) {
                return $request->_request('page', 1);
            });
            Paginator::currentPathResolver(function () use ($request) {
                $path = $request->path();
                if (!Util::str_startwith($path, '/')) {
                    $path = "/{$path}";
                }
                return $path;
            });
            Paginator::presenter(function (AbstractPaginator $_paginator) use ($request) {
                $_paginator->appends($request->all_get());
                /** @var \Illuminate\Contracts\Pagination\Paginator $paginator */
                $paginator = $_paginator;
                return new NewPaginationPresenter($paginator, $request);
            });
        });


    }

    private static function initCacheConfig()
    {
        $cache = new CacheConfig();
        $cache->setEncodeResolver(function ($prefix, $val) {

            $str = serialize($val);
            return gzcompress($str, 4);
        });
        $cache->setDecodeResolver(function ($prefix, $str_val) {

            if (ord(substr($str_val, 0, 1)) == 0x78) {
                $str = gzuncompress($str_val);
                return unserialize($str);
            } else {
                return unserialize($str_val);
            }
        });
        $cache->setMethodResolver(function ($prefix, $method) {

            return str_replace([
                'app\\Http\\Controllers\\',
                'app\\Console\\Commands\\',
                'app\\Libs\\',
                'app\\api\\',
            ], [
                'Ctrl:',
                'Cmd:',
                'Lib:',
                'Api:',
            ], $method);
        });
        $cache->setPreFixResolver(function ($pre_fix = null) {
            if (empty($pre_fix)) {
                $countly_pre = App::config('ENV_WEB.countly_pre', 'stock');
                $pre_fix = !empty($countly_pre) ? "R_{$countly_pre}" : "R_unknown";
            }
            return $pre_fix;
        });

        $cache->setBaseConfig(false, true, false);

        CacheConfig::setConfig(function () use ($cache) {
            return $cache;
        });
    }

    protected static function debugStrap($routerShutdown = true, $dispatchLoopStartup = true, $dispatchLoopShutdown = false, $preDispatch = false, $postDispatch = false, $preDisplay = true, $preWidget = true, $apiResult = true, $apiException = true, $runSql = true)
    {
        parent::debugStrap($routerShutdown, $dispatchLoopStartup, $dispatchLoopShutdown, $preDispatch, $postDispatch, $preDisplay, $preWidget, $apiResult, $apiException, $runSql);
    }

    /**
     * @param bool $debugMdel
     * @param bool $debugDelKey
     * @param bool $debugDelTag
     * @param bool $debugHit
     * @param bool $debugCache
     * @param bool $debugSkip
     * @param bool $logSql
     * @param bool $logCache
     * @throws AppStartUpError
     */
    public static function registerDebugEvent($debugMdel = true, $debugDelKey = true, $debugDelTag = true, $debugHit = false, $debugCache = true, $debugSkip = true, $logSql = false, $logCache = false)
    {
        if (true) {
            DbHelper::setOrmEventCallback(function ($type, $event) use ($logSql) {
                if ($type == 'QueryExecuted') {
                    /** @var QueryExecuted $event */
                    $sql_str = Util::prepare_query($event->sql, $event->bindings, '%%');
                    $_tag = "Orm::sql {$sql_str} ({$event->time}ms)";
                    $tag = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->debugTag($_tag) : $_tag;
                    if ($logSql || App::config('app.dev_log_sql', false)) {
                        $log = LogHelper::create("debug_sql");
                        $url = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->getRequestUri() : '';
                        $bindings_str = !empty($event->bindings) ? ", bindings:" . json_encode($event->bindings) : '';

                        $t = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->usedMilliSecond() : 0;
                        $t_str = ($t > 0 && $t < 1000) ? "{$t}ms" : ($t >= 1000 ? ($t / 1000) . "s" : '');
                        $log->debug("Orm::sql {$sql_str} ({$event->time}ms){$bindings_str}  [url:{$url}@{$t_str}]");
                    }
                    App::_D(['bindings' => $event->bindings], $tag);
                } else {
                    /** @var QueryExecuted $event */
                    $tag = Controller::_getRequestByCtx()->debugTag('DbHelper');
                    App::_D(['type' => $type, 'event' => $event], $tag);
                }
            });
        }

        foreach (['mdel' => $debugMdel, 'mhit' => false, 'delkey' => $debugDelKey, 'deltag' => $debugDelTag, 'hit' => $debugHit, 'cache' => $debugCache, 'skip' => $debugSkip] as $type => $enable) {
            $enable && CacheConfig::on($type, function (CacheEvent $ev) use ($logCache) {
                list($key, $method, $now, $tags, $timeCache, $update, $type) = [$ev->getKey(), $ev->getMethod(), $ev->getNow(), $ev->getTags(), $ev->getTimeCache(), $ev->getUpdate(), $ev->getType()];
                if ($ev->isUseStatic() && $type == 'hit') {
                    return;
                }

                $cache_time = $now - $update;
                $bytes = $ev->getBytes();
                $sTag = $ev->isUseStatic() ? '*' : '-';
                $sTag = $ev->isUseYac() ? '~' : $sTag;
                $sTag = $bytes > 0 ? "{$sTag}[" . Util::byte2size($bytes) . "]" : $sTag;
                $_tag = "Cache::{$type} {$method}?{$key} <{$timeCache}, {$cache_time}> {$sTag}";
                $tag = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->debugTag($_tag) : $_tag;
                if ($logCache || App::config('app.dev_log_cache', false)) {
                    $log = LogHelper::create("debug_cache");
                    $tags_str = !empty($tags) ? ", tags:" . join(',', $tags) : '';
                    $url = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->getRequestUri() : '';

                    $t = Controller::_getRequestByCtx() ? Controller::_getRequestByCtx()->usedMilliSecond() : 0;
                    $t_str = ($t > 0 && $t < 1000) ? "{$t}ms" : ($t >= 1000 ? ($t / 1000) . "s" : '');
                    $log->debug("Cache::{$type} {$method}?{$key} <{$timeCache}, {$cache_time}> {$sTag}  update:{$update}, now:{$now}{$tags_str}  [url:{$url}@{$t_str}]");
                }
                App::_D(['tags' => $tags, 'update' => $update, 'now' => $now], $tag);
            });
        }

        Boot::debugStrap();
    }

}