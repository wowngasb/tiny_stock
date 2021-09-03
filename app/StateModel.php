<?php
/**
 * Created by PhpStorm.
 * User: kongl
 * Date: 2018/2/28 0028
 * Time: 18:10
 */

namespace app;

use app\api\GraphQL_\Enum\StateEnum;

class StateModel extends Model
{

    public static function newItem(array $data, $log_op = true)
    {
        if (!isset($data['state'])) {
            $data['state'] = StateEnum::NORMAL_VALUE;
        }
        return parent::newItem($data, $log_op);
    }

}