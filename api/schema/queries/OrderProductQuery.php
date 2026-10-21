<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 11:30
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use common\models\Specification;
use common\models\User;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class OrderProductQuery
 * @package api\schema\queries
 */
class OrderProductQuery extends BasicQuery
{

    public function type()
    {
        return Types::orderProductPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'order_id' => [
                'type' => Type::nonNull(Type::int()),
            ],
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            $order = Order::find()
                ->where(['id' => $args['order_id']])
                ->one();
            $specification = Specification::find()
                ->joinWith('locations')
                ->where(['company_id' => $order->company_id])
                ->andWhere(['specification_location.location_id' => $order->location_id])
                ->all();
            $query = OrderProduct::find()->joinWith([
                'order',
                'product',
                'product.unit',
                'orderProductHistory',
                'orderProductHistory.oldProduct as oldProduct',
                'specificationsProduct' => function ($query) use ($specification) {
                    if ($specification) {
                        $query->onCondition([
                            'in', 'specification_product.specification_id', ArrayHelper::getColumn($specification, 'id')]);
                    }
                }
            ]);
            $query->where(['order_product.order_id' => $args['order_id']]);
            $arguments = PaginationType::arguments(
                $args,
                array_keys(OrderProduct::getTableSchema()->columns),
                [Product::class]
            );
            $query->groupBy('order_product.id');
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
