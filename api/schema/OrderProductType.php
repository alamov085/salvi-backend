<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.07.2019
 * Time: 11:24
 */

namespace api\schema;

use api\helpers\NumberHelper;
use common\models\OrderProduct;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class OrderProductType
 * @package api\schema
 */
class OrderProductType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Товары в заказе',
            'fields'      => function () {
                return [
                    'id'                  => Type::int(),
                    'order_id'            => Type::int(),
                    'product_id'          => Type::int(),
                    'product_uuid'        => Type::string(),
                    'quantity'            => Type::float(),
                    'price'               => Type::float(),
                    'total_sum'           => [
                        self::FIELD_TYPE    => Type::float(),
                        self::FIELD_RESOLVE => function (OrderProduct $model) {
                            return NumberHelper::convert($model->total_sum);
                        }
                    ],
                    'created_at'          => Type::int(),
                    'updated_at'          => Type::int(),
                    'product'             => [
                        'type'    => Types::product(),
                        'resolve' => function (OrderProduct $model) {
                            return $model->product;
                        }
                    ],
                    'view_only'           => Type::int(),
                    'name'                => Type::string(),
                    'custom'              => Type::boolean(),
                    'comment'             => Type::string(),
                    'avaliable_quantity'  => [
                        self::FIELD_TYPE    => Type::int(),
                        self::FIELD_RESOLVE => function (OrderProduct $model) {
                            if ($model->specificationsProduct) {
                                $status = ArrayHelper::getColumn($model->specificationsProduct, 'status');
                                $key = array_search(1, $status);
                                if ($key !== false) {
                                    return $model->specificationsProduct[$key]->quantity;
                                }
                            }
                            return -1;
                        }
                    ],
                    'product_status'      => [
                        self::FIELD_TYPE    => Type::int(),
                        self::FIELD_RESOLVE => function (OrderProduct $model) {
                            return $model->productStatus;
                        }
                    ],
                    'status'              => [
                        self::FIELD_TYPE    => Types::orderProductStatus(),
                        self::FIELD_RESOLVE => function (OrderProduct $model) {
                            return ArrayHelper::getValue(OrderProduct::getStatuses(), $model->productStatus);
                        }
                    ],
                    'approved'            => Type::int(),
                    'approved_quantity'   => Type::int(),
                    'orderProductHistory' => Types::orderProductHistory(),
                    'catalog'             => Type::boolean(),
                    'is_delete'           => Type::boolean(),
                    'is_strict_multiplicity'  => Type::boolean(),
                    'is_temperature_conditions'  => Type::boolean(),
                    'discount_price'  =>  Type::float(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
