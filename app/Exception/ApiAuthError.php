<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/29 0029
 * Time: 1:05
 */

namespace app\Exception;


use Tiny\Exception\AuthError;

/**
 * 认证失败
 * 缺少对应认证
 * @package app\Exception
 */
class ApiAuthError extends AuthError
{
    protected static $errno = 531;
}