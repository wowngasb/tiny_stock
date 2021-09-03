<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/11/10
 * Time: 14:31
 */

namespace app\Exception;

/**
 * 配置错误
 * 缺少对应功能的关键配置
 * @package app\Exception
 */
class ConfigError extends Error
{
    protected static $errno = 541;
}