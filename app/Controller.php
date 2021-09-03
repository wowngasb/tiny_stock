<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/24
 * Time: 17:21
 */

namespace app;

use app\api\Abstracts\AbstractApi;
use app\api\Abstracts\Api;
use app\Libs\UserAuth;
use Closure;
use Exception;
use Illuminate\Support\HtmlString;
use Tiny\Controller\BladeController;
use Tiny\Exception\AppStartUpError;
use Tiny\Interfaces\AuthInterface;
use Tiny\Interfaces\RequestInterface;
use Tiny\Interfaces\ResponseInterface;
use Tiny\OrmQuery\Q;

abstract class Controller extends BladeController
{
    const PRE_CFG_HASH_KEY = 'stock_pre_conf';

    const PRE_PATH_LIST = [
        'apiv100/',
    ];

    public static $max_csv_items = 5000;

    protected $num = 12;

    ################################################################
    ############################ beforeAction 函数 ##########################
    ################################################################

    public static function _D($data, $tags = null, $ignoreTraceCalls = 0)
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            $tags = $request->debugTag($tags);
        }
        App::_D($data, $tags, $ignoreTraceCalls);
    }

    public static function _extendWhereByCreateTime($create_time_s, $create_time_e, array $where)
    {
        if (!empty($create_time_s) && !empty($create_time_e) && $create_time_s <= $create_time_e) {
            $where['created_at'] = Q::whereBetween($create_time_s, $create_time_e);
        } elseif (empty($create_time_s) && $create_time_e > 0) {
            $where['created_at'] = Q::where($create_time_e, '<=');
        } elseif (empty($create_time_e) && $create_time_s > 0) {
            $where['created_at'] = Q::where($create_time_s, '>=');
        }
        return $where;
    }


    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        self::$_instance = $this;

        self::setBladePath(App::path(['resources', 'views']), self::getViewCachePath());

        $num = $this->_request('num', $this->num);
        $this->num = $num > 5 && $num < 100 ? intval($num) : $this->num;
        $this->assign('num', $this->num);

        return $params;
    }

    /** 组合一系列的条件参数 返回一个函数 依次调用参数中的函数 为 $table 增加检索条件
     * @param $func_list
     * @return Closure
     */
    public static function _buildWhereList($func_list)
    {
        $func_list = is_array($func_list) ? $func_list : func_get_args();
        return function ($table) use ($func_list) {
            foreach ($func_list as $func) {
                if (!empty($func)) {
                    $table = $func($table);
                }
            }
            return $table;
        };
    }

    ################################################################
    ############################ 不规范的辅助函数 ##########################
    ################################################################

    /**
     * @param Controller $ctrl
     * @return static
     */
    final public static function _createFromController(Controller $ctrl)
    {
        $obj = new static($ctrl->getRequest(), $ctrl->getResponse());
        $obj->_setAuth($ctrl->auth());
        $obj->beforeAction($ctrl->getRequest()->getParams());
        return $obj;
    }

    final public static function _createFromApi(AbstractApi $api)
    {
        $obj = new static($api->getRequest(), $api->getResponse());
        $obj->_setAuth($api->auth());
        $obj->beforeAction($api->getRequest()->getParams());
        return $obj;
    }

    /**
     * @param AuthInterface $auth
     */
    final public function _setAuth(AuthInterface $auth)
    {
        $this->_auth = $auth;
    }

    /** @var AuthInterface */
    private $_auth = null;

    final public function auth()
    {
        if (is_null($this->_auth)) {
            $this->_auth = $this->_initAuth();
        }
        return $this->_auth;
    }

    /**
     * @return AuthInterface
     */
    protected function _initAuth()
    {
        return new UserAuth($this);
    }

    /** @var Controller */
    private static $_instance = null;

    /**
     * @return null|AuthInterface
     */
    public static function _getAuthByCtx()
    {
        if (!empty(self::$_instance)) {
            return self::$_instance->auth();
        }
        return !empty(Api::$_instance) ? Api::$_instance->auth() : null;
    }

    /**
     * @return null|RequestInterface
     */
    public static function _getRequestByCtx()
    {
        if (!empty(self::$_instance)) {
            return self::$_instance->getRequest();
        }
        return !empty(AbstractApi::$_instance) ? AbstractApi::$_instance->getRequest() : null;
    }

    /**
     * @return null|ResponseInterface
     */
    public static function _getResponseByCtx()
    {
        if (!empty(self::$_instance)) {
            return self::$_instance->getResponse();
        }
        return !empty(Api::$_instance) ? Api::$_instance->getResponse() : null;
    }

    public static function getViewCachePath()
    {
        return App::cache_path(['view_tpl']);
    }

    protected function extendAssign(array $params)
    {
        $params['device_type'] = Util::device_type();
        $params['webname'] = App::config('ENV_WEB.name');
        $params['cdn'] = App::config('ENV_WEB.cdn');
        $params['cdn'] = $this->is_https() ? str_replace('http://', 'https://', $params['cdn']) : $params['cdn'];

        list($common_cdn, $webver) = Util::getCdn();
        $params['webver'] = !empty($params['webver']) ? $params['webver'] : $webver;
        $params['common_cdn'] = !empty($params['common_cdn']) ? $params['common_cdn'] : $common_cdn;
        $params['common_cdn'] = $this->is_https() ? str_replace('http://', 'https://', $params['common_cdn']) : $params['common_cdn'];

        if (empty($params['user'])) {
            if ($this->auth() && $this->auth()->check()) {
                $user = $this->auth()->user();
                $params['user'] = Util::try2array($user);
            } else {
                $params['user'] = null;
            }
        }


        if (empty($params['agentBrowser'])) {
            $params['agentBrowser'] = $this->getRequest()->agent_browser();
        }

        $self_uid = $this->auth()->id();

        $countly_pre = App::config('ENV_WEB.countly_pre', 'stock');
        $serial_number = Api::_tryGetSerialNumber($this->getRequest());

        $params['token'] = self::_encodeUserToken($self_uid, $countly_pre, $serial_number);

        return parent::extendAssign($params);
    }

    public static function _encodeUserToken($uid, $countly_pre, $serial_number)
    {
        $hash = Util::short_hash("{$countly_pre}_{$serial_number}");

        return UserAuth::_encode("{$hash}_{$uid}", 3600 * 24 * 90, UserAuth::ENCRYPT_TYPE_TOKEN);
    }

    public function csrf_token()
    {
        $agent = $this->getRequest()->_header('User-Agent', 'unknown agent');
        $token = $this->getRequest()->session_id() . '|' . md5($agent . '_csrf');
        return App::encrypt($token, 3600 * 24, '_csrf');
    }

    public function csrf_field()
    {
        $csrf_token = $this->csrf_token();
        $csrf_field = '<input type="hidden" name="_token" value="' . $csrf_token . '">';
        return new HtmlString($csrf_field);
    }

    /**
     * @throws AppStartUpError
     */
    final public function jump_index()
    {
        $url = App::url($this->getRequest(), ['', 'index', 'index']);
        App::redirect($this->getResponse(), $url);
    }

    public function is_post()
    {
        return Util::stri_cmp($this->getRequest()->getMethod(), 'post');
    }

    /**
     * @param $file
     * @param $data
     * @param callable|null $func
     * @param string $split
     * @param string $new_line
     * @param int $buffer_len
     * @return mixed
     * @throws Exception
     */
    public function exportCsv($file, $data, callable $func = null, $split = ",", $new_line = "\r\n", $buffer_len = 1000)
    {
        if (!Util::stri_endwith($file, '.csv')) {
            $file = "{$file}.csv";
        }
        $this->_addDownloadHeader($file);
        $this->getResponse()->sendHeader();

        $idx = 0;
        $buffer = [];
        foreach ($data as $val) {
            $item = !empty($func) ? $func($val) : $val;
            if ($idx == 0) {
                $buffer[] = self::_tryBuildCsvHeader($item, $split);
            }
            $buffer[] = join($split, array_values($item));
            $idx += 1;
            if (count($buffer) >= $buffer_len) {
                $line_str = join($new_line, $buffer) . $new_line;
                $this->getResponse()->appendBody(iconv("UTF-8", "GB2312//IGNORE", $line_str));
                $this->getResponse()->send();
                $buffer = [];
            }
        }
        if (!empty($buffer)) {
            $line_str = join($new_line, $buffer) . $new_line;
            $this->getResponse()->appendBody(iconv("UTF-8", "GB2312//IGNORE", $line_str));
            $this->getResponse()->send();
        }
        return $this->getResponse()->end();
    }

    private static function _tryBuildCsvHeader($val, $split = ',')
    {
        $cdx = 0;
        $headers = [];
        foreach ($val as $k => $v) {  // 第一行  尝试读取 key 信息 当作表头
            $cdx += 1;
            $tag = "col_{$cdx}";
            $dsl = is_numeric($k) ? [] : Util::dsl($k);
            $headers[] = !empty($dsl['base']) ? $dsl['base'] : $tag;
        }
        return join($split, $headers);
    }

    /**
     * @param $file
     * @throws Exception
     */
    protected function _addDownloadHeader($file)
    {
        $filename = $file;
        $encoded_filename = rawurlencode($filename);

        $ua = strtolower($this->getRequest()->_server('HTTP_USER_AGENT'));
        if (preg_match("/msie/", $ua) || preg_match("/edge/", $ua)) {
            $filename = iconv('UTF-8', 'GB2312//IGNORE', $filename);
            $encoded_filename = rawurlencode($filename);
            $fileHead = <<<EOT
attachment; filename="{$encoded_filename}"; filename*=utf-8''{$encoded_filename}
EOT;
        } elseif (preg_match("/firefox/", $ua)) {
            $fileHead = <<<EOT
attachment; filename="{$encoded_filename}"; filename*=utf-8''{$encoded_filename}
EOT;
        } else {
            $fileHead = <<<EOT
attachment; filename="{$encoded_filename}"; filename*=utf-8''{$encoded_filename}
EOT;
        }
        $this->getResponse()->addHeader("Content-Type: application/force-download")
            ->addHeader("Content-Type: application/octet-stream")
            ->addHeader("Content-Type: application/download")
            ->addHeader("Content-Disposition:inline;filename=\"{$file}\"")
            ->addHeader("Content-Transfer-Encoding: binary")
            ->addHeader("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT")
            ->addHeader("Cache-Control: must-revalidate, post-check=0, pre-check=0")
            ->addHeader("Pragma: no-cache")
            ->addHeader("Content-Disposition:{$fileHead}");
    }

}