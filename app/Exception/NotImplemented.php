<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/29 0029
 * Time: 23:03
 */

namespace app\Exception;

/**
 * 未实现对应功能
 * 一般不会直接抛出，用于断言
 * @package app\Exception
 */
class NotImplemented extends Error
{
    protected static $errno = 551;
}