<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/11 0011
 * Time: 16:34
 */

namespace app\Exception;

/**
 * 调用第三方接口失败
 * @package app\Exception
 */
class ApiError extends Error
{
    protected static $errno = 520;
}