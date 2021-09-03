<?php

$all_stock_file = dirname(__FILE__) . '/all_stock.txt';
$all_stock_lday = dirname(__FILE__) . '/all_stock_%t_lday.json';
$mini_m = 201509;
$all_stock = [];

$tdx_dir = 'D:\Program Files (x86)\tdx\vipdoc';
foreach (Util::load_stock_list($all_stock_file) as $item) {
    list($n, $c) = $item;
    $t = substr($c, 0, 2) == '00' || substr($c, 0, 2) == '30' ? 'sz' : 'sh';
    $tdx_day_file = $tdx_dir . "/{$t}/lday/{$t}{$c}.day";
    $all_stock[$c] = [
        'name' => $n,
        't' => $t,
        'code' => $c,
        'lday' => $tdx_day_file
    ];
}

echo "======= START CHECK DAY ALL " . count($all_stock) . " ========\n";
$idx = 0;
foreach ($all_stock as &$rtem) {
    $idx += 1;
    $rtem['lday_ok'] = is_file($rtem['lday']) ? 1 : 0;
    echo ($rtem['lday_ok'] ? '.' : 'x') . ($idx % 100 == 0 ? "\n" : '');
}
echo "\n======= END CHECK DAY ALL " . count($all_stock) . " ========\n\n";


foreach (['sh', 'sz'] as $t) {
    $ret = Util::dump_lday(array_filter($all_stock, fn($i) => $i['t'] == $t), $mini_m);
    $data = json_encode($ret, JSON_PRETTY_PRINT);
    unset($ret);
    file_put_contents(str_replace('%t', $t, $all_stock_lday), $data);
}


abstract class Util
{

    public static function dump_lday(array $stock_items, int $mini_m = 201509): array
    {
        $ret = [];
        foreach ($stock_items as $c => $item) {
            if (empty($item['lday_ok'])) {
                continue;
            }
            $data = file_get_contents($item['lday']);
            $mdata = Util::group_lday($data, $mini_m);

            $ret[$c] = [];
            foreach ($mdata as $m => $mdatum) {
                $gz = gzencode($mdatum, 9);
                $ret[$c][$m] = Util::safe_base64_encode($gz);
            }
        }
        return $ret;
    }

    public static function group_lday(string $data, $mini_m = 201509): array
    {
        $ret = [];
        $arr = str_split($data, 32);
        foreach ($arr as $d) {
            $t = unpack('L', $d)[1];
            $m = intval($t / 100);
            if ($m < $mini_m) {
                continue;
            }
            $ret[$m] = $ret[$m] ?? [];
            $ret[$m][$t] = $d;
        }

        foreach ($ret as $m => $ds) {
            ksort($ds);
            $ret[$m] = join('', $ds);
        }
        ksort($ret);
        return $ret;
    }

    public static function load_stock_list(string $filename): array
    {
        if (!is_file($filename)) {
            return [];
        }

        $lines = explode("\n", file_get_contents($filename));
        return array_filter(array_map(fn($i) => strpos($i, '/') ? explode('/', trim($i)) : null, $lines));
    }


    /**
     * @param int $length
     * @return string
     */
    public static function rand_str(int $length): string
    {
        if ($length <= 0) {
            return '';
        }
        $str = '';
        $tmp_str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($tmp_str) - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $tmp_str[rand(0, $max)];   //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * 加密函数
     * @param string $string 需要加密的字符串
     * @param string $key
     * @param int $expiry 加密生成的数据 的 有效期 为0表示永久有效， 单位 秒
     * @param string $salt
     * @param int $rnd_length 动态密匙长度 byte $rnd_length>=0，相同的明文会生成不同密文就是依靠动态密匙
     * @param int $chk_length 校验和长度 byte $rnd_length>=4 && $rnd_length><=16
     * @return string 加密结果 使用了 safe_base64_encode
     */
    public static function encode(string $string, string $key, int $expiry = 0, string $salt = 'salt', int $rnd_length = 2, int $chk_length = 4): string
    {
        return static::authcode($string, 'ENCODE', $key, $expiry, $salt, $rnd_length, $chk_length);
    }

    public static function byteToInt32WithLittleEndian(string $byte): int
    {
        $byte0 = isset($byte[0]) ? ord($byte[0]) : 0;
        $byte1 = isset($byte[1]) ? ord($byte[1]) : 0;
        $byte2 = isset($byte[2]) ? ord($byte[2]) : 0;
        $byte3 = isset($byte[3]) ? ord($byte[3]) : 0;
        return $byte3 * 256 * 256 * 256 + $byte2 * 256 * 256 + $byte1 * 256 + $byte0;
    }

    /**
     * @param string $_string
     * @param string $operation
     * @param string $_key
     * @param int $_expiry
     * @param string $salt
     * @param int $rnd_length 动态密匙长度 byte $rnd_length>=0，相同的明文会生成不同密文就是依靠动态密匙
     * @param int $chk_length 校验和长度 byte $rnd_length>=4 && $rnd_length><=16
     * @return string
     */
    public static function authcode(string $_string, string $operation, string $_key, int $_expiry, string $salt, int $rnd_length, int $chk_length): string
    {
        $rnd_length = $rnd_length > 0 ? $rnd_length : 0;
        $_expiry = $_expiry > 0 ? $_expiry : 0;
        $chk_length = $chk_length > 4 ? ($chk_length < 16 ? $chk_length : 16) : 4;
        $key = md5($salt . $_key . 'origin key');// 密匙
        $keya = md5($salt . substr($key, 0, 16) . 'key a for crypt');// 密匙a会参与加解密
        $keyb = md5($salt . substr($key, 16, 16) . 'key b for check sum');// 密匙b会用来做数据完整性验证

        if ($operation == 'DECODE') {
            $keyc = $rnd_length > 0 ? substr($_string, 0, $rnd_length) : '';// 密匙c用于变化生成的密文
            $crypt = $keya . md5($salt . $keya . $keyc . 'merge key a and key c');// 参与运算的密匙
            // 解码，会从第 $keyc_length Byte开始，因为密文前 $keyc_length Byte保存 动态密匙
            $string = static::safe_base64_decode(substr($_string, $rnd_length));
            $result = static::encodeByXor($string, $crypt);
            // 验证数据有效性
            $result_len_ = strlen($result);
            $expiry_at_ = $result_len_ >= 4 ? static::byteToInt32WithLittleEndian(substr($result, 0, 4)) : 0;
            $pre_len = 4 + $chk_length;
            $checksum_ = $result_len_ >= $pre_len ? bin2hex(substr($result, 4, $chk_length)) : 0;
            $string_ = $result_len_ >= $pre_len ? substr($result, $pre_len) : '';
            $tmp_sum = substr(md5($salt . $string_ . $keyb), 0, 2 * $chk_length);
            $test_pass = ($expiry_at_ == 0 || $expiry_at_ > time()) && $checksum_ == $tmp_sum;
            return $test_pass ? $string_ : '';
        } else {
            $keyc = $rnd_length > 0 ? static::rand_str($rnd_length) : '';// 密匙c用于变化生成的密文
            $checksum = substr(md5($salt . $_string . $keyb), 0, 2 * $chk_length);
            $expiry_at = $_expiry > 0 ? $_expiry + time() : 0;
            $crypt = $keya . md5($salt . $keya . $keyc . 'merge key a and key c');// 参与运算的密匙
            // 加密，原数据补充附加信息，共 8byte  前 4 Byte 用来保存时间戳，后 4 Byte 用来保存 $checksum 解密时验证数据完整性
            $string = static::int32ToByteWithLittleEndian($expiry_at) . hex2bin($checksum) . $_string;
            $result = static::encodeByXor($string, $crypt);
            return $keyc . static::safe_base64_encode($result);
        }
    }

    public static function safe_base64_decode(string $str): string
    {
        $str = strtr(trim($str), '-_', '+/');
        $last_len = strlen($str) % 4;
        $str = $last_len == 2 ? $str . '==' : ($last_len == 3 ? $str . '=' : $str);
        return base64_decode($str);
    }

    public static function encodeByXor(string $string, string $crypt): string
    {
        $string_length = strlen($string);
        $key_length = strlen($crypt);
        $result_list = [];
        $box = range(0, 255);
        $rndkey = [];
        // 产生密匙簿
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($crypt[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($i + $j + $box[$i] + $box[$j] + $rndkey[$i] + $rndkey[$j]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        // 核心加解密部分
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符
            $tmp_idx = ($box[$a] + $box[$j]) % 256;
            $result_list[] = chr(ord($string[$i]) ^ $box[$tmp_idx]);
        }

        return join('', $result_list);
    }

    public static function int32ToByteWithLittleEndian(int $int32): string
    {
        $int32 = abs($int32);
        $byte0 = $int32 % 256;
        $int32 = ($int32 - $byte0) / 256;
        $byte1 = $int32 % 256;
        $int32 = ($int32 - $byte1) / 256;
        $byte2 = $int32 % 256;
        $int32 = ($int32 - $byte2) / 256;
        $byte3 = $int32 % 256;
        return chr($byte0) . chr($byte1) . chr($byte2) . chr($byte3);
    }

    public static function safe_base64_encode(string $str): string
    {
        return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
    }

    /**
     * 解密函数 使用 配置 CRYPT_KEY 作为 key  成功返回原字符串  失败或过期 返回 空字符串
     * @param string $string 需解密的 字符串 safe_base64_encode 格式编码
     * @param string $key
     * @param string $salt
     * @param int $rnd_length 动态密匙长度 byte $rnd_length>=0，相同的明文会生成不同密文就是依靠动态密匙
     * @param int $chk_length 校验和长度 byte $rnd_length>=4 && $rnd_length><=16
     * @return string 解密结果
     */
    public static function decode(string $string, string $key, string $salt = 'salt', int $rnd_length = 2, int $chk_length = 4): string
    {
        return static::authcode($string, 'DECODE', $key, 0, $salt, $rnd_length, $chk_length);
    }


}
