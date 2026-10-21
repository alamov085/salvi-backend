<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 24.07.2019
 * Time: 16:08
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Company;
use common\models\User;
use Yii;

/**
 * Class MyCompanyQuery
 * @package api\schema\queries
 */
class MyCompanyQuery extends BasicQuery
{

    public function type()
    {
        return Types::company();
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $company = Company::find()->where(['id'=>$user->company_id])->one();
            return $company;
        };
    }
}
