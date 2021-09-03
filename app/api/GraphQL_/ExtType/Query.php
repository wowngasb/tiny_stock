<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2020-06
 */
namespace app\api\GraphQL_\ExtType;

use app\api\GraphQL_\Types;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class Query
 * Query
 * @package app\api\GraphQL_\ExtType
 */
class Query extends ObjectType
{

    public function __construct(array $_config = [], $type = null)
    {
        if (is_null($type)) {
            /** @var Types $type */
            $type = Types::class;
        }
        $config = [
            'description' => "Query",
            'fields' => []
        ];
        $config['fields']['hello'] = [
            'type' => $type::String(),
            'args' => [
                'name' => [
                    'type' => $type::String(),
                    'description' => "input you name",
                    'defaultValue' => "world",
                ],
            ],
        ];
        $config['fields']['deprecatedField'] = [
            'type' => $type::String(),
             'deprecationReason' => "This field is deprecated!",
        ];
        $config['fields']['fieldWithException'] = [
            'type' => $type::String(),
        ];
        
        $config['resolveField'] = function($value, $args, $context, ResolveInfo $info) {
            if (method_exists($this, $info->fieldName)) {
                return $this->{$info->fieldName}($value, $args, $context, $info);
            } else {
                return is_array($value) ? $value[$info->fieldName] : $value->{$info->fieldName};
            }
        };
        if (!empty($_config['fields'])) {
            $config['fields'] = array_merge($_config['fields'], $config['fields']);
        }
        parent::__construct($config);
    }
    
    public function hello($value, $args, $context, ResolveInfo $info)
    {
        return "Hello {$args['name']}, Your graphql-php endpoint is ready! Use GraphiQL to browse API";
    }

    public function deprecatedField()
    {
        return 'You can request deprecated field, but it is not displayed in auto-generated documentation by default.';
    }

    public function fieldWithException()
    {
        throw new \Exception("Exception message thrown in field resolver");
    }

}