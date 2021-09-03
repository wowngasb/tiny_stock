<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/3/6 0006
 * Time: 17:45
 */

namespace app\Exception;


use Exception;

/**
 * 不应运行到此处
 * 一般不会直接抛出，用于断言
 * @package app\Exception
 */
class NeverRunAtHereError extends Error
{
    protected static $errno = 529;

    public function __construct($message = "never run at here", Exception $previous = null)
    {
        parent::__construct($message, $previous);
    }
}