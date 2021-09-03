<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/1/22 0022
 * Time: 16:53
 */

namespace app;


use Tiny\Abstracts\AbstractClass as _AbstractClass;

abstract class AbstractClass extends _AbstractClass
{

    public static function _D($data, $tags = null, $ignoreTraceCalls = 0)
    {
        $request = Controller::_getRequestByCtx();
        if (!empty($request)) {
            $tags = $request->debugTag($tags);
        }
        App::_D($data, $tags, $ignoreTraceCalls);
    }

}