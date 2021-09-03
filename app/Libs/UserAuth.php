<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/24 0024
 * Time: 10:07
 */

namespace app\Libs;

use app\AbstractClass;
use app\Boot;
use app\Util;
use Tiny\Abstracts\AbstractContext;
use Tiny\Exception\AuthError;
use Tiny\Interfaces\AuthInterface;
use Tiny\Traits\EncryptTrait;

class UserAuth extends AbstractClass implements AuthInterface
{
    use EncryptTrait;

    const ENCRYPT_TYPE_COOKIE = 'ENCRYPT_TYPE_COOKIE';
    const ENCRYPT_TYPE_TOKEN = 'ENCRYPT_TYPE_TOKEN';

    /** @var AbstractContext */
    private $_ctx;
    private $_session_auth_id = 0;

    private $lastAttemptId = 0;

    private static $_cookie_expiry = 36000;  //10小时

    /**
     * @param AbstractContext $ctx
     */
    public function __construct(AbstractContext $ctx)
    {
        $this->_ctx = $ctx;
    }

    ##############################################################
    #################  实现 AuthInterface 接口  ##################
    ##############################################################
    private static function getOneById($id)
    {
        return [
            'uid' => $id,
            'name' => "name($id)"
        ];
    }

    private static function testUserPwd($account, $password)
    {
        return $account == $password ? 1 : 0;
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return array
     *
     * @throws AuthError
     */
    public function authenticate()
    {
        $user = $this->user();
        if (!empty($user)) {
            return $user;
        }

        throw new AuthError('no auth');
    }

    /**
     * Determine if the current user is authenticated.
     * @return bool
     */
    public function check()
    {
        $user = $this->user();
        return !empty($user);
    }

    /**
     * Determine if the current user is a guest.
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }


    /**
     * Get the currently authenticated user.
     * @return array
     */
    public function user()
    {
        $id = $this->_getAuthId();
        if (!empty($id)) {
            return self::_getUserById($id);
        }
        return [];
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int
     */
    public function id()
    {
        return $this->_getAuthId();
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param array $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        $tmp_id = $this->validate($credentials);
        if (!empty($tmp_id)) {
            $this->_session_auth_id = $tmp_id;
            return true;
        }
        return false;
    }

    /**
     * Log the given user ID into the application.
     *
     * @param int $id
     * @param bool $remember
     * @return array
     */
    public function loginUsingId($id, $remember = false)
    {
        $user = self::_getUserById($id);
        if (!empty($user)) {
            $this->_setAuthId($id, $remember);
            return $user;
        }
        return [];
    }

    /**
     * Get the last user we attempted to authenticate.
     *
     * @return array|null
     */
    public function getLastAttempted()
    {
        return !empty($this->lastAttemptId) ? self::_getUserById($this->lastAttemptId) : null;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param int $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        $user = self::_getUserById($id);
        if (!empty($user)) {
            $this->_session_auth_id = $id;
            return true;
        }

        return false;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        $this->_delAuthId();
    }

    /**
     * Validate a user's credentials.
     * 目前支持  name password 验证
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        return $this->attempt($credentials, false, false);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     * @param bool $login
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false, $login = true)
    {
        $id = self::_testUserByCredentials($credentials);
        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if (!empty($id)) {
            if ($login) {
                $this->loginUsingId($id, $remember);
            }
            $this->lastAttemptId = $id;
            return true;
        }

        return false;
    }

    ######################################################
    ################ 重写 EncryptTrait 方法 ##############
    ######################################################

    protected static function _getSalt()
    {
        // 修改这个 key 会影响所有 回话 加密解密  会导致 现有会话失效
        return 'uSEr';
    }


    ######################################################
    ################ 私有 方法 ##############
    ######################################################


    private function _getAuthCookieKey()
    {
        $name = Boot::_getSessionPreKey();
        $cookie_name = "{$name}?v=1.02";
        return 'U_' . Util::short_md5($cookie_name);
    }

    private function _setAuthId($id, $remember = false)
    {
        $this->_session_auth_id = $id;
        $name = $this->_getAuthCookieKey();
        $value = self::_encode($id, self::$_cookie_expiry, self::ENCRYPT_TYPE_COOKIE);
        $request = $this->_ctx->getRequest();
        $request->set_session($name, $value);

        $session_id = $request->session_id();

        self::setUserTypeLoginMap($this->_session_auth_id, $session_id, $request->client_ip(), $request->full());

        if ($remember) {
            $request->setcookie($name, $value, time() + self::$_cookie_expiry, '/', '', false, true);
        }
    }

    private function _getAuthId()
    {
        if (!empty($this->_session_auth_id)) {
            return $this->_session_auth_id;
        }
        $name = $this->_getAuthCookieKey();

        $auth_str = $this->_ctx->_cookie($name);
        if (empty($auth_str)) {
            $auth_str = $this->_ctx->_session($name);
        }
        $uid = intval(self::_decode($auth_str, self::ENCRYPT_TYPE_COOKIE));
        if (!empty($uid)) {
            $now = time();
            if (($uid + $now) % 60 <= 5) {  // 不用每次都设置
                $this->_setAuthId($uid);
            }

            $this->_session_auth_id = $uid;
        }
        return $uid;
    }

    private function _delAuthId()
    {
        $name = $this->_getAuthCookieKey();
        $request = $this->_ctx->getRequest();
        $request->set_session($name, '');
        $request->setcookie($name, '', time() + self::$_cookie_expiry, '/');
        $this->_session_auth_id = 0;
    }

    ######################################################
    ################ 管理 方法 ##############
    ######################################################

    private static function _userMapKey()
    {
        return Boot::_getSessionMapKey();
    }

    public static function setUserTypeLoginMap($uid, $session_id, $ip = '', $url = '')
    {
        $redis = self::_getRedisInstance();
        $preKey = self::_userMapKey();
        $type = 'user';

        $data = json_encode([
            'session_id' => $session_id,
            'uid' => $uid,
            'type' => $type,
            'account' => "account($uid)",
            'nick' => "nick($uid)",
            'user_slug' => "user_slug($uid)",
            '_update_' => time(),
            'date' => date('Y-m-d H:i:s'),
            'url' => $url,
            'ip' => $ip,
            'location' => Util::getIpLocation($ip),
        ]);

        $rKey = trim("{$preKey}:role:role_{$type}:{$session_id}");
        $redis->setex($rKey, self::$_cookie_expiry, $data);

        $uKey = trim("{$preKey}:user:user_{$uid}:{$session_id}");
        $redis->setex($uKey, self::$_cookie_expiry, $data);
    }

    public static function getUserLoginMapByUid($uid)
    {
        $redis = self::_getRedisInstance();
        $preKey = self::_userMapKey();
        $uKey = "{$preKey}:user:user_{$uid}:*";

        $list = $redis->keys($uKey);
        return self::_decodeUserLoginMap($list);
    }

    public static function getUserLoginMapByType($type)
    {
        $redis = self::_getRedisInstance();
        $preKey = self::_userMapKey();
        $rKey = "{$preKey}:role:role_{$type}:*";
        $list = $redis->keys($rKey);
        return self::_decodeUserLoginMap($list);
    }

    public static function delUserTypeLoginMap($uid, $session_id)
    {
        $redis = self::_getRedisInstance();
        $preKey = self::_userMapKey();
        $type = 'user';

        $rKey = trim("{$preKey}:role:role_{$type}:{$session_id}");
        $redis->set($rKey, '');
        $redis->del($rKey);

        $uKey = "{$preKey}:user:user_{$uid}:{$session_id}";
        $redis->set($uKey, '');
        $redis->del($uKey);
    }

    private static function _decodeUserLoginMap($list)
    {
        $redis = self::_getRedisInstance();
        $list = !empty($list) ? (array)$list : [];
        $ret = [];
        foreach ($list as $key) {
            $item = $redis->get($key);
            $ret[$key] = !empty($item) ? json_decode($item, true) : [];
        }
        return $ret;
    }

    ######################################################
    ################ 验证 方法 ##############
    ######################################################

    /**
     * @param array $credentials
     * @return int
     */
    private static function _testUserByCredentials(array $credentials = [])
    {
        list($account, $password) = [Util::v($credentials, 'account'), Util::v($credentials, 'password')];
        return self::testUserPwd($account, $password);
    }

    private static function _getUserById($id)
    {
        return self::getOneById($id);
    }

    /**
     * 判断当前用户 角色 指定权限 值
     * @param string $key
     * @return array
     */
    public function role($key)
    {
        return [];
    }
}