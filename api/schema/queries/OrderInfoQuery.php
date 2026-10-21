<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 11:18
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Locations;
use common\models\Order;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class OrderInfoQuery
 * @package api\schema\queries
 */
class OrderInfoQuery extends BasicQuery
{

    public function type()
    {
        return Types::order();
    }

    public function args()
    {
        return ['id' => Type::nonNull(Type::int())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (Yii::$app->user->can('viewOrderDetail')) {
                /** @var  $user User */
                $user = \Yii::$app->user->getIdentity();
                $query = Order::find()->joinWith(['costCenter', 'location', 'cart']);
                $query->where(['order.company_id' => $user->company_id]);
                $query->andWhere(['order.id' => $args['id']]);
                if (Yii::$app->user->can('viewAndCreateAllOrder')) {
                    $query->andWhere(['>', 'order.id', 0]);
                } elseif (Yii::$app->user->can('viewAndCreateLocationOrder')) {
                    if ($user->all_location) {
                        $companyLocation = Locations::find()
                            ->where(['company_id' => $user->company_id])
                            ->all();
                        $locations = ArrayHelper::getColumn($companyLocation, 'id');
                    } else {
                        $locations = ArrayHelper::getColumn($user->locations, 'location_id');
                    }
                    $query->andWhere(['in', 'order.location_id', $locations]);
                } elseif (Yii::$app->user->can('viewAndCreateCostOrder')) {
                    $query->andWhere([
                        'in',
                        'order.cost_center_id',
                        ArrayHelper::getValue($user, 'allCost', [])
                    ]);
                } else {
                    $query->andWhere(['order.created_by_user' => $user->id]);
                }
                $model = $query->one();

                if ($model) {
                    return $model;
                }
            }
            return [];
        };
    }
}
