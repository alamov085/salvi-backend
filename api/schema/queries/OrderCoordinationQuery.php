<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 18.07.2019
 * Time: 15:53
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Coordination;
use common\models\Order;
use common\models\OrderCoordination;
use common\models\OrderProduct;
use GraphQL\Type\Definition\Type;

/**
 * Class OrderCoordinationQuery
 * @package api\schema\queries
 */
class OrderCoordinationQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::orderCoordination());
    }

    public function args()
    {
        return ['order_id' => Type::nonNull(Type::int())];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $order = Order::find()
                ->joinWith(['budget'])
                ->where(['order.id' => $args['order_id']])->one();
            $product = OrderProduct::find()
                ->where(['order_id' => $args['order_id']])
                ->andWhere(['catalog' => 1])
                ->all();
            if ($order) {
                $query = OrderCoordination::find();
                $query->where([
                    'and',
                    ['order_id' => $args['order_id']],
                    ['additional_type' => Coordination::ADD_TYPE_NOTHING]
                ]);
                if ($order->non_budget_order && !$order->budget) {
                    $query->orWhere([
                        'and',
                        ['order_id' => $args['order_id']],
                        ['additional_type' => Coordination::ADD_TYPE_WITHOUT_BUDGET]
                    ]);
                }
                if ($order->budget && $order->budget->availableSum <= 0) {
                    $query->orWhere([
                        'and',
                        ['order_id' => $args['order_id']],
                        ['additional_type' => Coordination::ADD_TYPE_HIGHER_THAN_BUDGET]
                    ]);
                }
                if ($product) {
                    $query->orWhere([
                        'and',
                        ['order_id' => $args['order_id']],
                        ['additional_type' => Coordination::ADD_TYPE_CATALOG_PRODUCT]
                    ]);
                }
                return $query->all();
            }
            return [];
        };
    }
}
