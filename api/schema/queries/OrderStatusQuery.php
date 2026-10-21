<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.07.2019
 * Time: 10:56
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\Order;
use GraphQL\Type\Definition\Type;

/**
 * Class OrderStatusQuery
 * @package api\schema\queries
 */
class OrderStatusQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::status());
    }

    public function args()
    {
        return [];
    }

    public function resolve()
    {
        return function () {
            return Order::statuses();
        };
    }
}
