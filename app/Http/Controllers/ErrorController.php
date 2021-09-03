<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/26 0026
 * Time: 14:36
 */

namespace app\Http\Controllers;


use app\App;
use app\Util;
use Exception;
use PDOException;
use RedisException;
use Tiny\Controller\BladeController;

class ErrorController extends BladeController
{

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        self::setBladePath(App::path(['resources', 'views']), App::cache_path(['view_tpl']));

        return $params;
    }

    public function index()
    {
        return $this->page(404);
    }

    public function page($code = 500, Exception $ex = null)
    {
        $allow_code = [
            400, 401, 402, 403, 404, 405, 406,
            500, 501, 502, 503, 504,
        ];
        $trace_list = [];
        if (!empty($ex)) {
            $ex_msg = $ex->getMessage();
            $base_path = App::path();
            $trace_list = Util::trace_exception($ex, $base_path);
            if ($ex instanceof PDOException || $ex instanceof RedisException) {
                $fix_map = [
                    App::config('ENV_CRYPT_KEY') => '****',
                    App::config('ENV_DEVELOP_KEY') => '****',
                    App::config('ENV_REDIS.host') => '0.0.0.0',
                    App::config('ENV_REDIS.password') => '****',
                    App::config('ENV_DB.host') => '0.0.0.0',
                    App::config('ENV_DB.database') => '****',
                    App::config('ENV_DB.username') => '****',
                    App::config('ENV_DB.password') => '****',
                ];
                $ex_msg = Util::msg_fix($ex_msg, $fix_map);
                $trace_list = Util::trace_fix($trace_list, $fix_map);
            }
            $ex_type = get_class($ex);
        } else {
            $ex_msg = 'null';
            $ex_type = 'NULL';
        }

        $code = in_array($code, $allow_code) ? intval($code) : 500;
        $this->getResponse()->setResponseCode($code);
        return $this->view("errors.page", [
            'code' => $code,
            'ex_msg' => $ex_msg,
            'ex_type' => $ex_type,
            'trace_list' => $trace_list
        ]);
    }

}