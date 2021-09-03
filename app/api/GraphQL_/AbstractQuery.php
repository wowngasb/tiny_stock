<?php
/**
 * Created by table_graphQL.
 * 用于PHP Tiny框架
 * Date: 2020-06
 */
namespace app\api\GraphQL_;

use app\api\GraphQL_\ExtType\Query;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class AbstractQuery
 * AbstractQuery
 * @package app\api\GraphQL_
 */
abstract class AbstractQuery extends Query
{
    
    /**
     * 查询所有 分类 列表
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ArticleClassify
     */
    abstract public function classifyList($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 根据 classify_id 查询文章列表
     * _param Int $args['classify_id'] 分类 classify_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ArticleNews
     */
    abstract public function artList($rootValue, $args, $context, ResolveInfo $info);
    /**
    $classify_id = isset($args['classify_id']) ? intval($args['classify_id']) : 0;    //  Int  分类 classify_id (NonNull)
    
     */
    
    /**
     * 查询所有 帮助 列表
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed ArticleHelpDoc
     */
    abstract public function helpList($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 根据 id 查询 数据变更记录
     * _param Int $args['id'] 记录 id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed SiteOpRecord
     */
    abstract public function opRecord($rootValue, $args, $context, ResolveInfo $info);
    /**
    $id = isset($args['id']) ? intval($args['id']) : 0;    //  Int  记录 id (NonNull)
    
     */
    
    /**
     * 根据 id 查询 API请求记录
     * _param Int $args['id'] 记录 id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed SiteApiRecord
     */
    abstract public function apiRecord($rootValue, $args, $context, ResolveInfo $info);
    /**
    $id = isset($args['id']) ? intval($args['id']) : 0;    //  Int  记录 id (NonNull)
    
     */
    
    /**
     * 根据 uid 查询后台帐号信息
     * _param Int $args['uid'] 用户 uid (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed UserBase
     */
    abstract public function user($rootValue, $args, $context, ResolveInfo $info);
    /**
    $uid = isset($args['uid']) ? intval($args['uid']) : 0;    //  Int  用户 uid (NonNull)
    
     */
    
    /**
     * 查询当前帐号信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed UserBase
     */
    abstract public function curUser($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 查询当前App帐号信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed UserBase
     */
    abstract public function curAppUser($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 查询当前后台帐号信息
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed UserBase
     */
    abstract public function curAdminUser($rootValue, $args, $context, ResolveInfo $info);
    
    /**
     * 根据 content_id 查询 配置 信息
     * _param Int $args['content_id'] 配置 content_id (NonNull)
     * ---------------------
     * @param array $rootValue
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @return mixed SiteContentConfig
     */
    abstract public function content($rootValue, $args, $context, ResolveInfo $info);
    /**
    $content_id = isset($args['content_id']) ? intval($args['content_id']) : 0;    //  Int  配置 content_id (NonNull)
    
     */
    
}