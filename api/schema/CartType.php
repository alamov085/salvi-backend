<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 15:10
 */

namespace api\schema;

use common\models\Cart;
use GraphQL\Type\Definition\Type;

/**
 * Class CartType
 * @package api\schema
 */
class CartType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'order_id' => Type::int(),
                    'order' => Types::order(),
                    'user_id' => Type::int(),
                    'created_at' => Type::int(),
                    'updated_at' => Type::int(),
                    'company_id' => Type::int(),
                    'location_id' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function (Cart $model) {
                            if ($model->order) {
                                return $model->order->location_id;
                            }
                            return 0;
                        }
                    ],
                    'location_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Cart $model) {
                            if ($model->order) {
                                if ($model->order->location) {
                                    return $model->order->location->name;
                                }
                            }
                            return '';
                        }
                    ],
                    'cost_center_id' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function (Cart $model) {
                            if ($model->order) {
                                return $model->order->cost_center_id;
                            }
                            return 0;
                        }
                    ],
                    'cost_center_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Cart $model) {
                            if ($model->order) {
                                if ($model->order->costCenter) {
                                    return $model->order->costCenter->name;
                                }
                            }
                            return '';
                        }
                    ],
                    'total_sum' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Cart $model) {
                            if ($model->order) {
                                return $model->order->total_sum;
                            }
                            return 0;
                        }
                    ],
                    'total' => Type::string()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
