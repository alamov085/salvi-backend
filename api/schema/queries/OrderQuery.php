<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 10:33
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\CostCenter;
use common\models\Locations;
use common\models\Order;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class OrderQuery
 * @package api\schema\queries
 */
class OrderQuery extends BasicQuery
{

    public function type()
    {
        return Types::orderPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ],
            'budget_id' => [
                'type' => Type::int()
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'location_id' => [
                'type' => Type::int()
            ],
            'cost_center_id' => [
                'type' => Type::int()
            ],
            'date_from' => [
                'type' => Type::string()
            ],
            'date_to' => [
                'type' => Type::string()
            ],
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $query = Order::find()->joinWith(['costCenter', 'location', 'history']);
            $query->where(['order.company_id' => $user->company_id]);

            if(isset($args['location_id']) && !empty($args['location_id'])) {
                $query->andWhere(['order.location_id' => $args['location_id']]);
            }

            if(isset($args['cost_center_id']) && !empty($args['cost_center_id'])) {
                $query->andWhere(['order.cost_center_id' => $args['cost_center_id']]);
            }

            if(isset($args['date_from']) && !empty($args['date_from'])) {
                $query->andWhere(['>=', 'order.updated_at', strtotime($args['date_from']." 00:00:00")]);
            }

            if(isset($args['date_to']) && !empty($args['date_to'])) {
                $query->andWhere(['<=', 'order.updated_at', strtotime($args['date_to']." 23:59:59")]);
            }

            if (ArrayHelper::getValue($args, 'budget_id', 0)) {
                $query->andWhere(['order.budget_id' => $args['budget_id']]);
            } else {
                if (Yii::$app->user->can('viewOrder')) {
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
                } else {
                    $query->andWhere(['order.id' => 0]);
                }
            }
            if (isset($args['status']) && $args['status'] != -1) {
                $query->andWhere(['order.status' => $args['status']]);
            }
            $arguments = PaginationType::arguments(
                $args,
                array_keys(Order::getTableSchema()->columns),
                [Locations::class, CostCenter::class]
            );
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'order.id', $args['search']],
                    ['like', 'locations.name', $args['search']],
                    ['like', 'cost_center.name', $args['search']],
                    ['like', 'order.comment', $args['search']],
                ]);
            }

            $query->groupBy('order.id');
            $total = $query->count();

            $query->offset($arguments['perPage'] * $arguments['page'])
                ->limit($arguments['perPage'])
                ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
            return [
                'model' => $query->all(),
                'total' => $total,
            ];
        };
    }
}
