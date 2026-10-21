<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 18:38
 */

namespace api\schema;

use common\models\SpecificationProduct;
use GraphQL\Type\Definition\Type;

/**
 * Class SpecificationProductType
 * @package api\schema
 */
class SpecificationProductType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Товары спецификация',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'price' => [
                        'type' => Type::float(),
                        'resolve' => function (SpecificationProduct $model) {
                            $result = $model->price;
                            return number_format($result, 2, '.', '');
                        }
                    ],
                    'markup' => Type::float(),
                    'manual_markup' => Type::int(),
                    'product' => [
                        'type' => Types::product(),
                        'resolve' => function (SpecificationProduct $model) {
                            return $model->product;
                        }
                    ],
                    'use_markup' => Type::int(),
                    'quantity' => Type::int(),
                    'status' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
