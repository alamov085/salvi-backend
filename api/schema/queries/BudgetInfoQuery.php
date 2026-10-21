<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 20.07.2019
 * Time: 13:58
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Budget;
use common\models\User;
use GraphQL\Type\Definition\Type;

/**
 * Class BudgetInfoQuery
 * @package api\schema\queries
 */
class BudgetInfoQuery extends BasicQuery
{

    public function type()
    {
        return Types::budget();
    }

    public function args()
    {
        return ['id' => Type::nonNull(Type::int())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = \Yii::$app->user->getIdentity();
            $query = Budget::find()
                ->joinWith(['costCenters', 'locations'])
                ->where(['budget.id' => $args['id']])
                ->andWhere(['budget.company_id' => $user->company_id]);
            $result = $query->one();
            return $result;
        };
    }
}
