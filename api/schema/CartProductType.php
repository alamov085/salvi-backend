<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 15:14
 */

namespace api\schema;

use api\helpers\NumberHelper;
use common\models\CartProduct;
use GraphQL\Type\Definition\Type;

/**
 * Class CartProductType
 * @package api\schema
 */
class CartProductType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Товары в корзине',
            'fields'      => function () {
                return [
                    'id'             => Type::int(),
                    'cart_id'        => Type::int(),
                    'product_id'     => Type::int(),
                    'product_uuid'   => Type::string(),
                    'quantity'       => [
                        self::FIELD_TYPE    => Type::int(),
                        self::FIELD_RESOLVE => function (CartProduct $model) {
                            if ($model->new_quantity) {
                                return $model->new_quantity;
                            }
                            return $model->quantity;
                        }
                    ],
                    'new_quantity'   => Type::int(),
                    'old_quantity'   => [
                        self::FIELD_TYPE    => Type::int(),
                        self::FIELD_RESOLVE => function (CartProduct $model) {
                            return $model->quantity;
                        }
                    ],
                    'price'          => Type::float(),
                    'total_sum'      => [
                        self::FIELD_TYPE    => Type::float(),
                        self::FIELD_RESOLVE => function (CartProduct $model) {
                            return NumberHelper::convert($model->total_sum);
                        }
                    ],
                    'status'         => Type::int(),
                    'product'        => Types::product(),
                    'newProduct'     => [
                        self::FIELD_TYPE    => Type::boolean(),
                        self::FIELD_RESOLVE => function (CartProduct $model) {
                            if ($model->status == CartProduct::STATUS_NEW) {
                                return true;
                            }
                            return false;
                        }
                    ],
                    'changeQuantity' => [
                        self::FIELD_TYPE    => Type::boolean(),
                        self::FIELD_RESOLVE => function (CartProduct $model) {
                            if ($model->status == CartProduct::STATUS_CHANGE_QUANTITY) {
                                return true;
                            }
                            return false;
                        }
                    ],
                    'last_update'    => Type::boolean(),
                    'is_delete'      => Type::boolean(),
                    'is_specification'      => Type::boolean(),
                    'is_strict_multiplicity'      => Type::boolean(),
                    'is_temperature_conditions'      => Type::boolean(),
                    'discount_price'      =>  Type::float(),
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
