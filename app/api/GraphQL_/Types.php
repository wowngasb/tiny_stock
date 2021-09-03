<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2020-06
 */
namespace app\api\GraphQL_;

//import query classes
use app\api\GraphQL_\ExtType\Query;

//import Type Definition classes
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\Type;

/**
 * Class Types
 * Acts as a registry and factory for types.
 * @package app\api\GraphQL_
 */
class Types
{

    ####################################
    ########  root query type  #########
    ####################################

    private static $_mQuery = null;

    /**
     * 必须实现 AbstractQuery 中的虚方法 才可以使用完整的查询 此方法需要重写
     * @param array $config
     * @param mixed $type
     * @return Query
     */
    public static function Query(array $config = [], $type = null)
    {
        return self::$_mQuery ?: (self::$_mQuery = new Query($config, $type));
    }

    ####################################
    ########## internal types ##########
    ####################################

    public static function boolean()
    {
        return Type::boolean();
    }

    /**
     * @return \GraphQL\Type\Definition\FloatType
     */
    public static function float()
    {
        return Type::float();
    }

    /**
     * @return \GraphQL\Type\Definition\IDType
     */
    public static function id()
    {
        return Type::id();
    }

    /**
     * @return \GraphQL\Type\Definition\IntType
     */
    public static function int()
    {
        return Type::int();
    }

    /**
     * @return \GraphQL\Type\Definition\StringType
     */
    public static function string()
    {
        return Type::string();
    }

    /**
     * @param Type $type
     * @return ListOfType
     */
    public static function listOf($type)
    {
        return new ListOfType($type);
    }

    /**
     * @param Type $type
     * @return NonNull
     */
    public static function nonNull($type)
    {
        return new NonNull($type);
    }
}