<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/5/7 0007
 * Time: 10:16
 */

namespace app\api\Abstracts;

use app\App;
use app\Controller;
use app\Libs\UserAuth;
use app\Util;
use Tiny\Abstracts\AbstractApi as AbstractApiAlias;
use Tiny\Interfaces\AuthInterface;

abstract class AbstractApi extends AbstractApiAlias
{

    const SHORT_CACHE_TIME = 5;


    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        if (!empty($params['params_'])) {
            $params = array_merge($params, json_decode($params['params_'], true));
        }
        self::$_instance = $this;

        if (isset($params['page'])) {
            $params['page'] = $params['page'] > 1 ? intval($params['page']) : 1;
        }
        if (isset($params['num'])) {
            $params['num'] = $params['num'] > 1 && $params['num'] <= 200 ? intval($params['num']) : 20;
        }

        return $params;
    }

    public static function _D($data, $tags = null, $ignoreTraceCalls = 0)
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            $tags = $request->debugTag($tags);
        }
        App::_D($data, $tags, $ignoreTraceCalls);
    }

    public static function _tryFindFirstSuperId($uid = 0)
    {
        $uid = intval($uid);
        $uid = $uid > 0 ? $uid : 0;
        if (!empty($uid)) {
            return $uid;
        }

        return 1;
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

    /**
     * @param AuthInterface $auth
     */
    final public function _setAuth(AuthInterface $auth)
    {
        $this->_auth = $auth;
    }

    /** @var AbstractApi */
    public static $_instance = null;

    public function _hookAuthUid($uid)
    {
        $uid = intval($uid);
        $this->auth()->onceUsingId($uid);
        return $this;
    }

    public static function _tryGetSerialNumber($req)
    {
        $headers = !empty($req) ? $req->lower_header() : [];
        $serial_number = Util::v($headers, 'serial-number', '');

        if (empty($serial_number)) {
            $agent = Util::v($headers, 'user-agent', '');
            if (stripos($agent, "YCSerial/") > 0) {
                preg_match("/YCSerial/([\w.-_]+)+/i", $agent, $ver);
                $serial_number = !empty($ver[1]) ? $ver[1] : '';
            }
        }

        if (empty($serial_number) && !empty($req)) {
            $serial_number = $req->_session('default-serial-number', '');
            if (empty($serial_number) || !Util::stri_startwith($serial_number, 'def_')) {
                $serial_number = 'def_' . md5(Util::rand_str(16));
                $req->_session('default-serial-number', $serial_number, true);
            }
        }
        return $serial_number;
    }
}