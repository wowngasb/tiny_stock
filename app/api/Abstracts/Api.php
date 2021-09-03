<?php
/**
 * Created by PhpStorm.
 * User: kl
 * Date: 2017/10/25
 * Time: 15:38
 */

namespace app\api\Abstracts;


use app\Exception\ApiAuthError;
use app\Util;
use Tiny\Exception\AppStartUpError;
use Tiny\Plugin\DevAuthController;

abstract class Api extends AbstractApi
{

    /**
     * @param array $params
     * @return array
     * @throws ApiAuthError
     * @throws AppStartUpError
     */
    public function beforeAction(array $params)
    {
        $params = parent::beforeAction($params);

        if (DevAuthController::_checkDevelopKey($this->getRequest())) {
            // 开发模式 允许设置 hook_id 以指定用户权限来执行API
            $hook_id = Util::v($params, 'hook_id');
            if (!empty($hook_id)) {
                if (!$this->auth()->onceUsingId($hook_id)) {
                    throw new ApiAuthError("Api auth()->onceUsingId Error with hook_id:{$hook_id}");
                }
            } else {
                $uid = self::_tryFindFirstSuperId();
                !empty($uid) && $this->auth()->onceUsingId($uid);
            }
        }

        return $params;
    }

}