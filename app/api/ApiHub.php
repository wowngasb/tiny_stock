<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/8/14
 * Time: 12:03
 */

namespace app\api;

use app\api\Abstracts\AbstractApi;
use app\Exception\ApiParamsError;
use app\Util;

/**
 * 测试API
 * @package app\api
 */
class ApiHub extends AbstractApi
{

    protected static $detail_log = true;

    ################################################################
    ###########################  beforeAction ##########################
    ################################################################

    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);
        if (isset($params['name'])) {
            $params['name'] = trim(strval($params['name']));
        }

        if (Util::stri_cmp('testSum', $this->_getActionName())) {
            $params['a'] = intval($params['a']);
            $params['b'] = intval($params['b']);
        }

        if (isset($params['id'])) {
            $params['id'] = intval($params['id']);
        }

        return $params;
    }


    ################################################################
    ###########################  Auto build ##########################
    ################################################################

    private static function _trySaveFile($file_name, $file_str)
    {
        if (is_file($file_name)) {
            $tmp = file_get_contents($file_name);
            if (md5(trim($tmp)) == md5(trim($file_str))) {
                return;
            }
        }
        file_put_contents($file_name, $file_str, LOCK_EX);
    }


    public static function _dumpGraphQLUnionTypes($base_dir, $file, $overwrite = true)
    {
        $query = <<<EOT
{
    __schema {
      types {
        kind
        name
        possibleTypes {
          name
        }
      }
    }
}
EOT;

        $api = GraphQLApi::_createGraphQLApi();

        $result = $api->exec($query);
        if (is_object($result) && is_callable([$result, 'toArray'])) {
            $result = call_user_func_array([$result, 'toArray'], []);
        }

        $js_str = json_encode($result['data']);

        $f = "{$base_dir}/{$file}";
        if (!is_file($f) || $overwrite) {
            if (is_file($f)) {
                $tmp = file_get_contents($f);
                if (md5(trim($tmp)) == md5(trim($js_str))) {
                    return;
                }
            }
            self::_trySaveFile($f, $js_str);
        }
    }

    /**
     * 构建 GraphQLFragments
     * @param string $base_dir
     * @param string $file
     * @param array $fragmentsMap
     * @param bool $overwrite
     */
    public static function _dumpGraphQLFragments($base_dir, $file, array $fragmentsMap, $overwrite = true)
    {
        $js_str = "";
        foreach ($fragmentsMap as $key => $item) {
            $q_str = !empty($item[0]) ? $item[0] : '';
            $q_arr = !empty($item[1]) ? $item[1] : [];
            if (empty($q_str)) {
                continue;
            }
            $js_str .= "\nexport const {$key} = `\n{$q_str}";

            $js_str .= "\n";
            foreach ($q_arr as $dep) {
                $js_str .= '${' . $dep . '}' . "\n";
            }
            $js_str .= "`;\n";
        }

        $f = "{$base_dir}/{$file}";
        if (!is_file($f) || $overwrite) {
            if (is_file($f)) {
                $tmp = file_get_contents($f);
                if (md5(trim($tmp)) == md5(trim($js_str))) {
                    return;
                }
            }
            self::_trySaveFile($f, $js_str);
        }
    }


    ################################################################
    ###########################  测试 API ##########################
    ################################################################

    /**
     * api hello
     * @param string $name
     * @return array
     */
    public function hello($name = 'world')
    {
        $msg = "test log name={$name}";
        self::debug($msg, __METHOD__, __CLASS__, __LINE__);
        self::info($msg, __METHOD__, __CLASS__, __LINE__);
        self::warn($msg, __METHOD__, __CLASS__, __LINE__);
        self::error($msg, __METHOD__, __CLASS__, __LINE__);
        self::fatal($msg, __METHOD__, __CLASS__, __LINE__);

        return ['info' => "Hello, {$name}!", 'ip' => $this->client_ip()];
    }

    /**
     * 测试异常
     * @param int $id
     * @return array
     * @throws ApiParamsError
     */
    public function testError($id)
    {
        if ($id <= 0) {
            throw new ApiParamsError('id must gt 0');
        }
        return ['id' => $id, 'info' => 'some info'];
    }

    /**
     * 测试求和
     * @param int $a
     * @param int $b
     * @return array
     */
    public function testSum($a, $b)
    {
        $sum = $a + $b;
        $msg = "test log a={$a} b={$b}, sum={$sum}";
        self::debug($msg, __METHOD__, __CLASS__, __LINE__);
        self::info($msg, __METHOD__, __CLASS__, __LINE__);
        self::warn($msg, __METHOD__, __CLASS__, __LINE__);
        self::error($msg, __METHOD__, __CLASS__, __LINE__);
        self::fatal($msg, __METHOD__, __CLASS__, __LINE__);
        return ['data' => $sum];
    }

    public function testQuery($page = 0, $num = 20, array $sort_option = ['room_id', 'asc'], $room_id = 0, array $room_id_list = [], $room_title = '')
    {
        $skip = ($page - 1) * $num;

        $total = $skip;
        $list = [];
        $rst = ['list' => $list, 'total' => $total];

        self::$detail_log && self::debugArgs(func_get_args(), __METHOD__, __CLASS__, __LINE__);
        self::$detail_log && self::debugResult($rst, __METHOD__, __CLASS__, __LINE__);
        return $rst;
    }

}