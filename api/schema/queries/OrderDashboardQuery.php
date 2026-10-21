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
class OrderDashboardQuery extends BasicQuery
{

    public function type()
    {
        return Types::orderDashboardPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'status' => [
                'type' => Type::int(),
            ],
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = \Yii::$app->user->getIdentity();
            $query = Order::find()->joinWith(['costCenter', 'location', 'company']);
            if ($user->company_id) {
                $query->where(['order.company_id' => $user->company_id]);
            } else {
                $query->where(['in', 'order.company_id', $user->getManagerCompanies()]);
            }
            if (isset($args['status']) && $args['status'] != -1) {
                $query->andWhere(['order.status' => $args['status']]);
            }
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
            $arguments = PaginationType::arguments(
                $args,
                array_keys(Order::getTableSchema()->columns),
                [CostCenter::class, Locations::class]
            );
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'order.id', $args['search']],
                    ['like', 'locations.name', $args['search']],
                    ['like', 'cost_center.name', $args['search']],
                    ['like', 'order.comment', $args['search']],
                    ['like', 'company.short_name', $args['search']],
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
