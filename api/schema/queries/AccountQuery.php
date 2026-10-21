<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 01.07.2019
 * Time: 16:02
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\User;

/**
 * Class AccountQuery
 * @package api\schema\queries
 */
class AccountQuery extends BasicQuery
{

    public function type()
    {
        return Types::user();
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            return User::find()->where(['id' => \Yii::$app->user->getId()])->one();
        };
    }
}