<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/26
 * Time: 2:25
 */

namespace app\Libs;


use app\Model;
use IP;

class IpAddrHelper extends Model
{
    const CLIENT_PRE_KEY = 'client';
    const CLIENT_CACHE_TIME = 24 * 3600;
    private static $_cache_ip_map = [];

    public static function cacheClientIp($agent_rand, $uid, $agent, $ip_str, $ip_addr, $ref_host, $ref_url, $client_id, $ttl = null)
    {
        $agent_rand = trim($agent_rand);
        $uid = trim($uid);
        $ttl = is_null($ttl) ? self::CLIENT_CACHE_TIME : intval($ttl);
        $redis = self::_getRedisInstance();

        $val = json_encode([
            'agent' => $agent,
            'ip_str' => $ip_str,
            'ip_addr' => $ip_addr,
            'ref_host' => $ref_host,
            'ref_url' => $ref_url,
            'client_id' => $client_id,
            'time' => time(),
        ]);
        $key = self::CLIENT_PRE_KEY . ":{$agent_rand}:{$uid}";
        $redis->setex($key, $ttl, $val);
    }

    public static function loadClientIp($agent_rand, $uid)
    {
        $agent_rand = trim($agent_rand);
        $uid = trim($uid);
        $redis = self::_getRedisInstance();
        $key = self::CLIENT_PRE_KEY . ":{$agent_rand}:{$uid}";
        $json_str = $redis->get($key);
        return !empty($json_str) ? json_decode($json_str, true) : [];
    }

    public static function clearClientIp($agent_rand, $uid)
    {
        $agent_rand = trim($agent_rand);
        $uid = trim($uid);
        $redis = self::_getRedisInstance();
        $key = self::CLIENT_PRE_KEY . ":{$agent_rand}:{$uid}";
        $redis->expireAt($key, time() + 600);
        // $redis->delete($key);
    }

    public static function ipFind($ip)
    {
        $ip = trim(strval($ip));
        if (empty($ip) || $ip === '0.0.0.0') {
            return '未知';
        }
        if ($ip === '127.0.0.1') {
            return '本机地址';
        }

        if (!empty(self::$_cache_ip_map[$ip])) {
            return self::$_cache_ip_map[$ip];
        }
        $ip_addr = self::_cacheDataManager(__METHOD__, self::_hashKey([
            'ip_addr' => $ip
        ]), function () use ($ip) {
            include_once(PLUGIN_PATH . 'ip/IP.class.php');
            $record_id = !empty($ip) ? IP::find($ip) : null;
            if (is_array($record_id) && isset($record_id[1])) {
                $ip_addr = isset($record_id[2]) ? "{$record_id[0]} {$record_id[1]} {$record_id[2]}" : "{$record_id[0]} {$record_id[1]}";
            } else {
                $ip_addr = '未知';
            }
            return $ip_addr;
        }, function ($data) {
            return !empty($data) && $data != '未知' ? true : 10;
        }, 3600);

        self::$_cache_ip_map[$ip] = $ip_addr;

        return self::$_cache_ip_map[$ip];
    }

}