<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 11:47
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Company;
use common\models\User;
use Yii;

/**
 * Class CompanyInfoQuery
 * @package api\schema\queries
 */
class CompanyInfoQuery extends BasicQuery
{

    public function type()
    {
        return Types::companyInfo();
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
            $query = Company::find()->where(['id' => $user->company_id])->one();
            return $query;
        };
    }
}
