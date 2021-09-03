<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2020-06
 */
namespace app\api\GraphQL_\Enum;

use GraphQL\Type\Definition\EnumType;

/**
 * Class StateEnum
 * 通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE
 * @package app\api\GraphQL_\Enum
 */
class StateEnum extends EnumType
{
    const NOTDEL_ENUM = "NOTDEL";
    const NOTDEL_VALUE = 3;
    const NORMAL_ENUM = "NORMAL";
    const NORMAL_VALUE = 1;
    const FROZEN_ENUM = "FROZEN";
    const FROZEN_VALUE = 2;
    const UNKNOWN_ENUM = "UNKNOWN";
    const UNKNOWN_VALUE = 0;
    const DELETED_ENUM = "DELETED";
    const DELETED_VALUE = 4;
    const EXPIRED_ENUM = "EXPIRED";
    const EXPIRED_VALUE = 8;
    const RESERVE_ENUM = "RESERVE";
    const RESERVE_VALUE = 16;

    const ALL_ENUM_TYPE = ["NOTDEL", "NORMAL", "FROZEN", "UNKNOWN", "DELETED", "EXPIRED", "RESERVE"];
    const ALL_ENUM_VALUE = [3, 1, 2, 0, 4, 8, 16];
    const ALL_ENUM_MAP = ['NOTDEL' => 3, 'NORMAL' => 1, 'FROZEN' => 2, 'UNKNOWN' => 0, 'DELETED' => 4, 'EXPIRED' => 8, 'RESERVE' => 16];
        
    public function __construct(array $_config = [])
    {
        $config = [
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
            'values' => []
        ];
        $config['values']['NOTDEL'] = [
            'value' => '3',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        $config['values']['NORMAL'] = [
            'value' => '1',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        $config['values']['FROZEN'] = [
            'value' => '2',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        $config['values']['UNKNOWN'] = [
            'value' => '0',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        $config['values']['DELETED'] = [
            'value' => '4',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        $config['values']['EXPIRED'] = [
            'value' => '8',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        $config['values']['RESERVE'] = [
            'value' => '16',
            'description' => "通用状态类型 0:未知(用于检索所有) UNKNOWN,  1:正常 NORMAL,  2:冻结 FROZEN,  3:非删除 NOTDEL,  4:删除 DELETED,  8:过期 EXPIRED,  16:备用 RESERVE",
        ];
        
        if (!empty($_config['values'])) {
            $config['values'] = array_merge($config['values'], $_config['values']);
        }
        parent::__construct($config);
    }

}