<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/25
 * Time: 9:36
 */

namespace app;

use app\api\GraphQL_\Enum\StateEnum;
use app\Console\Kernel;
use app\Libs\IpAddrHelper;
use Exception;
use Tiny\OrmQuery\Q;
use Tiny\Util as UtilAlias;

class Util extends UtilAlias
{
    const QUERY_SUB_LIMIT = 300;

    const EMPTY_DATETIME = '0000-00-00 00:00:00';

    const EMPTY_DATETIME_ISO = '0000-00-00T00:00:00Z';

    const TIMER_TYPE_MAP = [
        'minute' => 60,
        '5minute' => 300,
        'hour' => 3600,
        'day' => 86400,
    ];

    ###########################################################################
    ################################    GraphQL    ############################
    ###########################################################################

    /**
     * @param array $list
     * @param $num
     * @param callable $func
     * @return array
     */
    public static function chuckWithMergeRet(array $list, $num, callable $func)
    {
        $num = $num >= 1 ? intval($num) : 1;
        if (empty($list)) {
            return [];
        }

        $rets = [];
        $tmp = [];
        $idx = 0;
        while (!empty($list)) {
            $tmp[] = array_shift($list);
            if (count($tmp) >= $num) {
                $t = $func($idx, $tmp);
                $rets = array_merge($rets, $t);
                $tmp = [];
                $idx += 1;
            }
        }
        if (!empty($tmp)) {
            $t = $func($idx, $tmp);
            $rets = array_merge($rets, $t);
        }
        return $rets;
    }


    public static function num2alpha($intNum, $isLower = true)
    {
        $num26 = base_convert($intNum, 10, 26);
        $addCode = $isLower ? 49 : 17;
        $result = '';
        for ($i = 0; $i < strlen($num26); $i++) {
            $code = ord($num26{$i});
            if ($code < 58) {
                $result .= chr($code + $addCode);
            } else {
                $result .= chr($code + $addCode - 39);
            }
        }
        return $result;
    }

    public static function alpha2num($strAlpha)
    {
        if (ord($strAlpha{0}) > 90) {
            $startCode = 97;
            $reduceCode = 10;
        } else {
            $startCode = 65;
            $reduceCode = -22;
        }
        $num26 = '';
        for ($i = 0; $i < strlen($strAlpha); $i++) {
            $code = ord($strAlpha{$i});
            if ($code < $startCode + 10) {
                $num26 .= $code - $startCode;
            } else {
                $num26 .= chr($code - $reduceCode);
            }
        }
        return (int)base_convert($num26, 26, 10);
    }


    public static function https($url, $request = null)
    {
        if (empty($url)) {
            return $url;
        }
        $request = empty($request) ? Controller::_getRequestByCtx() : $request;
        $host = !empty($request) ? $request->host() : '';
        if (!empty($host) && self::str_startwith($url, '/')) {
            $url = "{$host}{$url}";
        }

        if (!self::stri_startwith($url, 'http://') && !self::stri_startwith($url, 'https://')) {
            $url = "http://{$url}";
        }

        $is_https = !empty($request) && $request->is_https();
        return $is_https ? str_replace('http://', 'https://', $url) : $url;
    }

    public static function httpsArr(array $data, array $keys, $request = null)
    {
        foreach ($keys as $key) {
            $data[$key] = self::https(self::v($data, $key, ''), $request);
        }
        return $data;
    }

    public static function encodeJsonWithGz($data, $level = 6)
    {
        $json_str = json_encode($data);
        return gzcompress($json_str, $level);
    }

    public static function encodeStrWithGz($data, $level = 6)
    {
        $data = strval($data);
        return gzcompress($data, $level);
    }

    public static function decodeJsonWithGz($str_val)
    {
        if (ord(substr($str_val, 0, 1)) == 0x78) {
            $str = gzuncompress($str_val);
            return json_decode($str, true);
        } else {
            return json_decode($str_val, true);
        }
    }

    /**
     * @param array $md5List
     * @return string
     */
    public static function getMd5List($md5List)
    {
        $md5List = !empty($md5List) ? (array)$md5List : [];
        $str = '';
        foreach ($md5List as $md5) {
            $str .= base64_decode($md5);
        }

        return md5($str);
    }

    /**
     * @param int $len
     * @return string
     */
    public static function getRandFileName($len = 24)
    {
        $arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
        $size = count($arr);
        $str = "";
        for ($i = 0; $i <= $len; $i++) {
            $str = $str . $arr[rand(0, $size - 1)];
        }
        return $str;
    }

    public static function _dateByTimer($timer_count, $timer_type = 'minute')
    {
        $timer_interval = is_numeric($timer_type) ? intval($timer_type) : Util::v(self::TIMER_TYPE_MAP, Util::trimlower($timer_type), 60);
        return date('Y-m-d H:i:s', $timer_count * $timer_interval);
    }

    public static function rangeHour($start, $end)
    {
        $start = is_numeric($start) ? intval($start) : strtotime($start);
        $end = is_numeric($end) ? intval($end) : strtotime($end);

        $ret = [];
        for ($i = $start; $i <= $end; $i += 3600) {
            $ret[] = static::ceilToHour($i);
        }
        return $ret;
    }

    public static function ceilToHour($time_str)
    {
        $t = is_numeric($time_str) ? intval($time_str) : strtotime($time_str);
        $pre_hour = date('Y-m-d H:00:00', $t);
        $t = $t > strtotime($pre_hour) ? strtotime($pre_hour) + 3600 : $t;
        return date('Y-m-d H:00:00', $t);
    }

    public static function accMapByKey($map1, $map2)
    {
        $keys = array_keys(array_merge($map1, $map2));
        $ret = [];
        foreach ($keys as $key) {
            $val1 = !empty($map1[$key]) ? $map1[$key] : 0;
            $val2 = !empty($map2[$key]) ? $map2[$key] : 0;
            $ret[$key] = $val1 + $val2;
        }
        return $ret;
    }

    const MIN_DATE_PER_DAY = 20170101;

    public static function autoFixObj($obj)
    {
        // ('/' + obj if not obj.startswith('/') else obj).split('://', 1)[-1].split('/', 1)[-1]
        $obj = explode("#", $obj, 2)[0];
        $obj = explode("?", $obj, 2)[0];

        $obj = Util::str_startwith($obj, '/') ? $obj : "/{$obj}";
        $arr1 = explode('://', $obj, 2);
        $obj = $arr1[count($arr1) - 1];
        $arr2 = explode('/', $obj, 2);
        return $arr2[count($arr2) - 1];
    }

    /**
     * @param int $stime
     * @param int $etime
     * @param int $split_sec
     * @param array $fmt
     * @param int $offset
     * @return array
     */
    public static function splitTimeRangeSec($stime, $etime, $split_sec, array $fmt = ['Y-m-d H:i:s', 'Y-m-d H:i:s'], $offset = 0)
    {
        if ($etime - $stime < $split_sec) {
            return [
                [date($fmt[0], $stime), date($fmt[1], $etime)]
            ];
        }
        list($stime, $etime) = [$stime - $offset, $etime - $offset];

        $range_arr = [];
        $time = $stime;
        while ($time < $etime) {
            $tmp = intval(($time + $split_sec) / $split_sec) * $split_sec;
            $tmp = $tmp >= $etime ? $etime : $tmp;

            $last = $tmp % $split_sec == 0 ? $tmp - 1 : $tmp;
            $range_arr[] = [date($fmt[0], $time + $offset), date($fmt[1], $last + $offset)];
            $time = $tmp;
        }
        return $range_arr;
    }

    public static function getMonthNum($date1, $date2, $tags = '-')
    {
        $date1 = explode($tags, $date1);
        $date2 = explode($tags, $date2);
        return abs($date1[0] - $date2[0]) * 12 + abs($date1[1] - $date2[1]);
    }

    public static function encodeByRFC3986($arg_1)
    {
        $encodeOssObject = "";
        $arrayList = explode("/", $arg_1);
        for ($i = 0; $i < count($arrayList); $i++) {
            $tmp = rawurlencode($arrayList[$i]);
            $encodeOssObject = $encodeOssObject . $tmp;
            if ($i !== count($arrayList) - 1) {
                $encodeOssObject = $encodeOssObject . "/";
            }
        }
        return $encodeOssObject;
    }

    /**
     * @param $expiration_date
     * @return array
     */
    public static function whereByExpirationDate($expiration_date)
    {
        return self::whereByRange('expiration_date', $expiration_date);
    }

    public static function whereByIntervalTime($interval_time)
    {
        return self::whereByRange('interval_time', $interval_time);
    }

    /**
     * @param $created_at
     * @return array
     */
    public static function whereByCreatedAt($created_at)
    {
        return self::whereByRange('created_at', $created_at);
    }

    /**
     * @param $updated_at
     * @return array
     */
    public static function whereByUpdatedAt($updated_at)
    {
        return self::whereByRange('updated_at', $updated_at);
    }

    public static function whereByLoginLast($login_last)
    {
        return self::whereByRange('login_last', $login_last);
    }

    public static function whereByNumMax($num_max)
    {
        return self::whereByRange('num_max', $num_max, true);
    }

    public static function whereByPerDay($per_day)
    {
        return self::whereByRange('per_day', $per_day, true);
    }

    public static function whereByViewCount($view_count)
    {
        return self::whereByRange('view_count', $view_count, true);
    }

    /**
     * @param $key
     * @param $range
     * @param bool $as_int
     * @return array
     */
    public static function whereByRange($key, $range, $as_int = false)
    {
        return [
            "{$key}#between" => Q::whereBetween(Util::v($range, 'lower'), Util::v($range, 'upper'), function () use ($range, $as_int) {
                return Util::check_range($range, $as_int);
            }),
            "{$key}#gte-lower" => Q::where(Util::v($range, 'lower'), '>=', function () use ($range, $as_int) {
                list($lower, $upper) = Util::get_range($range, $as_int);
                return !empty($lower) && empty($upper);
            }),
            "{$key}#lte-lower" => Q::where(Util::v($range, 'upper'), '<=', function () use ($range, $as_int) {
                list($lower, $upper) = Util::get_range($range, $as_int);
                return !empty($upper) && empty($lower);
            }),
        ];
    }

    /**
     * @param $state
     * @return array
     */
    public static function whereByState($state)
    {
        return [
            'state#_eq' => Q::where($state, '=', function () use ($state) {
                return $state > 0 && $state != StateEnum::NOTDEL_VALUE;
            }),
            'state#_in' => Q::whereIn([StateEnum::NORMAL_VALUE, StateEnum::FROZEN_VALUE], function () use ($state) {
                return $state == StateEnum::NOTDEL_VALUE;
            }),
        ];
    }

    ##########################
    ######## 修复 相关 ########
    ##########################

    public static function _readTableItem($op_table, $op_prival, $autoTo = '')
    {
        $op_table = Util::trimlower($op_table);
        $model_cls = Util::v(Util::v(Model::$_op_record_map, $op_table, []), 'cls', '');
        $item = null;
        if (!empty($model_cls)) {
            $item = call_user_func_array("{$model_cls}::getOneById", [$op_prival]);
        }
        if (!empty($autoTo)) {
            $item = Util::try2array($item);
            unset($item['created_at'], $item['updated_at']);
            $del_keys = Util::v(Util::v(Model::$_op_record_map, $op_table, []), 'skip', []);
            foreach ($del_keys as $del_key) {
                unset($item[$del_key]);
            }
            if ($autoTo == 'json') {
                return !empty($item) ? json_encode($item) : '{}';
            } elseif ($autoTo == 'array') {
                return !empty($item) ? $item : [];
            }
        }

        return $item;
    }

    public static function allDepsInObj($deps, $obj)
    {
        if (empty($deps)) {
            return true;
        }

        $deps = self::build_map_set($deps);
        foreach ($deps as $dep) {
            if (empty($obj[$dep])) {
                return false;
            }
        }
        return true;
    }

    public static function parentDepsFromMenu($depList, $objMenu)
    {
        $deps = [];
        foreach ($objMenu as $menu) {
            $access_value = Util::v($menu, 'access_value', '');
            if (!empty($menu['dep']) && in_array($access_value, $depList)) {
                $deps = array_merge($menu['dep']);
            }
            $children = Util::v($menu, 'children', []);
            foreach ($children as $child) {
                $_access_value = Util::v($child, 'access_value', '');
                if (!empty($child['dep']) && in_array($_access_value, $depList)) {
                    $deps = array_merge($child['dep']);
                }
            }
        }
        return Util::build_map_set(array_map(function ($v) {
            return join('.', $v);
        }, $deps));
    }

    ##########################
    ######## CMD 相关 ########
    ##########################

    public static function cmd_commands($args, $print)
    {
        $s = microtime(true);
        $ret = Kernel::listCommands();
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        foreach ($ret as $cmd => $doc) {
            $print && $print("{$cmd} => {$doc}");
        }
        return <<<EOT
####################################
list_commands: {$used}s;
EOT;
    }

    public static function cmd_schedules($args, $print)
    {
        $s = microtime(true);
        $ret = Kernel::listSchedules();
        foreach ($ret as $cmd => $item) {
            $print && $print("{$cmd} => " . json_encode($item));
        }
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
list_schedules: {$used}s;
EOT;
    }

    /**
     * @param $args
     * @param $print
     * @return string
     * @throws Exception
     */
    public static function cmd_crontab($args, $print)
    {
        $s = microtime(true);
        $ts = !empty($args[2]) && intval($args[2]) > 0 ? intval($args[2]) : time();

        $ret = Kernel::runSchedule($ts, false, function ($line, $tag) use ($print) {
            $print && $print("[{$tag}] {$line}");
        });
        if (App::dev()) {
            var_dump($ret);
        }

        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
crontab: {$used}s;
EOT;
    }

    public static function cmd_run($args, $print)
    {
        $s = microtime(true);
        $cmd = !empty($args[2]) ? strval($args[2]) : '';
        $ts = !empty($args[3]) && intval($args[3]) > 0 ? intval($args[3]) : time();

        $ret = Kernel::runScheduleSite($cmd, $ts, false, function ($line, $tag) use ($print) {
            $print && $print("[{$tag}] {$line}");
        });
        if (App::dev()) {
            var_dump($ret);
        }

        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
####################################
commands: {$used}s;
EOT;
    }

    public static function cmd_clear($args, $print)
    {
        $s = microtime(true);

        $type = !empty($args[2]) ? trim($args[2]) : 'route|console';

        if (stripos($type, 'view') !== false) {
            $view_path = Controller::getViewCachePath();
            $view_list = static::getfiles($view_path);
            foreach ($view_list as $view_cache => $v_name) {
                if (!static::str_startwith($v_name, '.') && !empty($view_cache) && is_file($view_cache)) {
                    $print && $print("delete View Cache {$v_name}");
                    unlink($view_cache);
                }
            }
        }

        if (stripos($type, 'route') !== false) {
            $route_path = Boot::getRouteCachePath();
            $route_list = static::getfiles($route_path);
            foreach ($route_list as $route_cache => $r_name) {
                if (!static::str_startwith($r_name, '.') && !empty($route_cache) && is_file($route_cache)) {
                    $print && $print("delete Route Cache {$r_name}");
                    unlink($route_cache);
                }
            }
        }

        if (stripos($type, 'console') !== false) {
            $console_file = Boot::getConsoleStorageFile();
            if (!empty($console_file) && is_file($console_file)) {
                $print && $print("delete Console Storage File => {$console_file} ");
                unlink($console_file);
            }
        }
        $used = intval((microtime(true) - $s) * 1000) / 1000;
        return <<<EOT
####################################
####################################
clear: {$used}s;
EOT;
    }

    public static function cmd_version($args, $print)
    {
        $root_path = App::app()->path();
        $ver = static::load_git_ver($root_path);
        return <<<EOT
Git Info:
root_path => {$root_path}
git_ref   => {$ver['git_ref']}
ref_type  => {$ver['ref_type']}
git_ver   => {$ver['git_ver']}
EOT;
    }


    public static function dev_host($default_host = '')
    {
        $dev_host = App::config('ENV_WEB.devsrv');
        $dev_srv = App::config('app.dev_srv');
        if (!empty($dev_srv)) {
            $dev_host = $dev_srv;
        }

        if (empty($dev_host)) {
            $dev_host = $default_host;
        }
        $dev_host = Util::stri_startwith($dev_host, 'https://') || Util::stri_startwith($dev_host, 'http://') ? $dev_host : "http://{$dev_host}";
        return Util::str_endwith('/', $dev_host) ? $dev_host : "{$dev_host}/";
    }

    public static function tryAddHost($host, $path, array $args = [])
    {
        if (empty($path)) {
            return '';
        }

        $host = self::stri_startwith($host, 'http://') || self::stri_startwith($host, 'https://') ? $host : "http://{$host}";
        $host = self::str_endwith($host, '/') ? $host : "{$host}/";

        $path = self::str_startwith($path, '/') ? substr($path, 1) : $path;
        $uri = self::stri_startwith($path, 'http://') || self::stri_startwith($path, 'https://') ? $path : "{$host}{$path}";
        if (!empty($args)) {
            $uri = self::build_get($uri, $args);
        }
        return $uri;
    }

    public static function getBaseCdn($host)
    {
        $cdn = App::config('ENV_WEB.cdn');
        $cdn = !empty($cdn) ? trim($cdn) : "http://{$host}";
        if (!self::stri_startwith($cdn, 'http://') && !self::stri_startwith($cdn, 'https://')) {
            $cdn = "http://{$cdn}";
        }
        $assets_ver = App::config('ENV_WEB.ver');
        $assets_ver = !empty($assets_ver) ? $assets_ver : App::config('services.cdn_ver');
        $webver = $assets_ver;
        return [Util::trimlower($cdn), $webver];
    }

    public static function getCdn()
    {
        $cdn = App::config('services.common_cdn');

        if (!self::stri_startwith($cdn, 'http://') && !self::stri_startwith($cdn, 'https://')) {
            $cdn = "http://{$cdn}";
        }
        $assets_ver = App::config('ENV_WEB.ver');
        $assets_ver = !empty($assets_ver) ? $assets_ver : App::config('services.cdn_ver');
        $webver = $assets_ver;
        return [Util::trimlower($cdn), $webver];
    }

    public static function topN(array $arr, $top, $cmp = 1)
    {
        if (empty($arr)) {
            return [];
        }
        uasort($arr, function ($a, $b) use ($cmp) {
            return $a == $b ? 0 : ($a < $b ? $cmp : -$cmp);
        });

        if ($top <= 0 || $top >= count($arr)) {
            return $arr;
        }

        $top_map = [];
        foreach ($arr as $k => $v) {
            $top_map[$k] = $v;
            if (count($top_map) == $top) {
                return $top_map;
            }
        }
        return $arr;
    }

    public static function device_type($user_agent = '')
    {
        $user_agent = !empty($user_agent) ? $user_agent : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        //全部变成小写字母
        $agent = strtolower($user_agent);
        $type = 'pc';
        //分别进行判断
        if (strpos($agent, 'iphone') !== false) {
            $type = 'ios';
        }
        if (strpos($agent, 'ipad') !== false) {
            $type = 'ipad';
        }
        if (strpos($agent, 'android') !== false) {
            $type = 'android';
        }
        return $type;
    }

    const DEVICE_TYPE_TAG_MAP = [
        'ard' => 'android',
        'pad' => 'ipad',
        'ios' => 'iphone',
        'wap' => 'mobile',
        'win' => 'window',
        'mac' => 'macintosh',
        'lnx' => 'linux',
        'web' => 'pc'
    ];

    public static function device_type_tag($user_agent = '')
    {
        $user_agent = !empty($user_agent) ? $user_agent : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        $agent = strtolower($user_agent);

        if (self::is_mobile($user_agent)) {
            if (strpos($agent, 'iphone') !== false) {
                return 'ios';
            }
            if (strpos($agent, 'ipad') !== false) {
                return 'pad';
            }
            if (strpos($agent, 'android') !== false) {
                return 'ard';
            }
            return 'wap';
        } else {
            if (strpos($agent, 'window') !== false) {
                return 'win';
            }
            if (strpos($agent, 'macintosh') !== false) {
                return 'mac';
            }
            if (strpos($agent, 'linux') !== false) {
                return 'lnx';
            }
            return 'web';
        }
    }

    public static function is_weixin($user_agent = '')
    {
        $user_agent = !empty($user_agent) ? $user_agent : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        $is_weixin = false;
        if (!empty($user_agent) && strpos($user_agent, 'MicroMessenger') !== false) {
            $is_weixin = true;
        }
        return $is_weixin;
    }

    public static function is_qq($user_agent = '')
    {
        $user_agent = !empty($user_agent) ? $user_agent : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
        $is_qq = false;
        if (!empty($user_agent) && strpos($user_agent, ' QQ/') !== false) {
            $is_qq = true;
        }
        return $is_qq;
    }

    public static function is_mobile($user_agent = '')
    {
        $user_agent = !empty($user_agent) ? $user_agent : (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

        $mobile_agents = ['xiaomi', "240x320", "acer", "acoon", "acs-", "abacho", "ahong", "airness", "alcatel", "amoi", "android", "anywhereyougo.com", "applewebkit/525", "applewebkit/532", "asus", "audio", "au-mic", "avantogo", "becker", "benq", "bilbo", "bird", "blackberry", "blazer", "bleu", "cdm-", "compal", "coolpad", "danger", "dbtel", "dopod", "elaine", "eric", "etouch", "fly ", "fly_", "fly-", "go.web", "goodaccess", "gradiente", "grundig", "haier", "hedy", "hitachi", "htc", "huawei", "hutchison", "inno", "ipad", "ipaq", "ipod", "jbrowser", "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2", "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-", "lge-", "lge9", "longcos", "maemo", "mercator", "meridian", "micromax", "midp", "mini", "mitsu", "mmm", "mmp", "mobi", "mot-", "moto", "nec-", "netfront", "newgen", "nexian", "nf-browser", "nintendo", "nitro", "nokia", "nook", "novarra", "obigo", "palm", "panasonic", "pantech", "philips", "phone", "pg-", "playstation", "pocket", "pt-", "qc-", "qtek", "rover", "sagem", "sama", "samu", "sanyo", "samsung", "sch-", "scooter", "sec-", "sendo", "sgh-", "sharp", "siemens", "sie-", "softbank", "sony", "spice", "sprint", "spv", "symbian", "tablet", "talkabout", "tcl-", "teleca", "telit", "tianyu", "tim-", "toshiba", "tsm", "up.browser", "utec", "utstar", "verykool", "virgin", "vk-", "voda", "voxtel", "vx", "wap", "wellco", "wig browser", "wii", "windows ce", "wireless", "xda", "xde", "zte"];

        if (empty($user_agent)) {
            return false;
        }
        $is_mobile = false;
        foreach ($mobile_agents as $device) {//这里把值遍历一遍，用于查找是否有上述字符串出现过
            if (stristr($user_agent, $device)) { //stristr 查找访客端信息是否在上述数组中，不存在即为PC端。
                $is_mobile = true;
                break;
            }
        }

        return $is_mobile;
    }

    public static function page_seo($title = '', $description = null, $keywords = null)
    {
        $title = trim("{$title}");
        return [
            'title' => $title,
            'description' => !is_null($description) ? trim("{$description}") : $title,
            'keywords' => !is_null($keywords) ? trim("{$keywords}") : $title,
        ];
    }


    /**
     * @param string $url 服务的url地址
     * @param string $query 请求串.
     * @return string
     */
    public static function sock_post($url, $query)
    {
        $data = '';
        $info = parse_url($url);
        $fp = fsockopen($info['host'], 80, $errno, $errstr, 30);
        if (!$fp) {
            return $data;
        }
        $head = 'POST ' . $info['path'] . " HTTP/1.0\r\n";
        $head .= 'Host: ' . $info['host'] . "\r\n";
        $head .= 'Referer: http://' . $info['host'] . $info['path'] . "\r\n";
        $head .= "Content-type: application/x-www-form-urlencoded\r\n";
        $head .= 'Content-Length: ' . strlen(trim($query)) . "\r\n";
        $head .= "\r\n";
        $head .= trim($query);
        fputs($fp, $head);
        $header = '';
        while ($str = trim(fgets($fp, 4096))) {
            $header .= $str;
        }
        while (!feof($fp)) {
            $data .= fgets($fp, 4096);
        }

        return $data;
    }

    public static function getFile($url, $save_file, $type = 1)
    {
        if (empty($url) || empty($save_file)) {
            return false;
        }
        if (is_file($save_file)) {
            return true;
        }
        $content = '';

        $tmp_file = "{$save_file}.tmp";
        is_file($tmp_file) && @unlink($tmp_file);

        //获取远程文件所采用的方法
        if ($type == 1) {
            $fp2 = @fopen($tmp_file, 'wb');

            $ch = curl_init();
            $timeout = 30;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_FILE, $fp2);
            curl_exec($ch);

            curl_close($ch);
            fclose($fp2);
        } elseif ($type == 2) {
            ob_start();
            if (self::stri_startwith($url, 'https://')) {
                $uri = explode('://', $url, 2)[1];
                $url = 'http://' . $uri;
            }

            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();

            $fp2 = @fopen($tmp_file, 'a');
            fwrite($fp2, $content);
            fclose($fp2);
        }

        is_file($save_file) && @unlink($save_file);
        @rename($tmp_file, $save_file);

        unset($content);
        return true;
    }

    /**
     * ip 地域查询
     * @param string $ip
     * @param int $timeCache
     * @return string
     */
    public static function getIpLocation($ip, $timeCache = 36000)
    {
        if (empty($ip)) {
            return '未知';
        }
        if (Util::stri_cmp($ip, '127.0.0.1') || Util::stri_cmp($ip, '0.0.0.0')) {
            return '本机地址';
        }

        return IpAddrHelper::ipFind($ip);
    }

}