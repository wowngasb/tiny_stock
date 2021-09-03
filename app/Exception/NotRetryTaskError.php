<?php


namespace app\Exception;


class NotRetryTaskError extends ApiError
{
    protected static $errno = 699;
}