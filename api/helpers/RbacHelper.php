<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 28.08.2019
 * Time: 11:40
 */

namespace api\helpers;

use common\models\AuthAssignment;
use common\models\AuthItemChild;
use common\models\ChatUser;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class RbacHelper
 * @package api\helpers
 */
class RbacHelper
{
    /**
     * @param $permission
     * @return array|AuthAssignment[]|AuthItemChild[]|ChatUser[]|ActiveRecord[]
     */
    public static function getUserListByPermission($permission)
    {
        $roles = AuthItemChild::find()->where(['child' => $permission])->all();
        if ($roles) {
            $roles = ArrayHelper::getColumn($roles, 'parent');
            $users = AuthAssignment::find()->where(['in', 'item_name', $roles])->all();
            if ($users) {
                $users = ArrayHelper::getColumn($users, 'user_id');
                return $users;
            }
        }
        return [0];
    }
}
