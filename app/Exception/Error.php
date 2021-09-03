<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/29 0029
 * Time: 1:05
 */

namespace app\Exception;

use Tiny\Exception\Error as _Error;

/**
 * 错误基类
 * 一般不会直接抛出
 * @package app\Exception
 */
abstract class Error extends _Error
{
    protected static $errno = 520;

}