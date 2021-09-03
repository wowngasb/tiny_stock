<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2016/10/10 0010
 * Time: 10:10
 */

namespace app\Exception;

/**
 * 接口参数错误
 * @package app\Exception
 */
class ApiParamsError extends ApiError
{
    protected static $errno = 521;

}