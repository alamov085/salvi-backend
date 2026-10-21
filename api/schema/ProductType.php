<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2019
 * Time: 11:09
 */

namespace api\schema;

use common\models\Product;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class ProductType
 * @package api\schema
 */
class ProductType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Товары',
            'fields'      => function () {
                return [
                    'id'                     => Type::int(),
                    'uuid'                   => Type::string(),
                    'sku'                    => Type::string(),
                    'uktzed'                 => Type::string(),
                    'buy_price'              => [
                        'type'    => Type::string(),
                        'resolve' => function (Product $model) {
                            return number_format($model->buy_price, 2);
                        }
                    ],
                    'price'                  => [
                        'type'    => Type::string(),
                        'resolve' => function (Product $model) {
                            return number_format($model->priceCatalog, 2);
                        }
                    ],
                    'name'                   => Type::string(),
                    'description'            => [
                        self::FIELD_TYPE    => Type::string(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            return '' . $model->description;
                        }
                    ],
                    'unit_uuid'              => Type::string(),
                    'unit_id'                => Type::int(),
                    'quantity'               => [
                        'type'    => Type::int(),
                        'resolve' => function (Product $model) {
                            return 1;
                        }
                    ],
                    'unit'                   => [
                        'type'    => Types::unit(),
                        'resolve' => function (Product $model) {
                            return $model->unit;
                        }
                    ],
                    'category'               => [
                        'type'    => Type::string(),
                        'resolve' => function (Product $model) {
                            return ArrayHelper::getValue($model->categories, '0.name');
                        }
                    ],
                    'category_uuid'          => [
                        'type'    => Type::string(),
                        'resolve' => function (Product $model) {
                            return ArrayHelper::getValue($model->categories, '0.uuid');
                        }
                    ],
                    'category_id'            => [
                        'type'    => Type::string(),
                        'resolve' => function (Product $model) {
                            return ArrayHelper::getValue($model->categories, '0.id');
                        }
                    ],
                    'img'                    => [
                        'type'    => Type::string(),
                        'resolve' => function (Product $model) {
                            if ($model->image) {
                                return Yii::$app->urlManager->createAbsoluteUrl(['/product/' . $model->image->name]);
                            }
                            return Yii::$app->urlManager->createAbsoluteUrl(['/images/default.jpg']);
                        }
                    ],
                    'images'                 => [
                        self::FIELD_TYPE    => Type::listOf(Types::image()),
                        self::FIELD_RESOLVE => function (Product $model) {
                            return $model->images;
                        }
                    ],
                    'isSpecificationProduct' => [
                        self::FIELD_TYPE    => Type::boolean(),
                        self::FIELD_RESOLVE => function (Product $product) {
                            return $product->isSpecificationProduct;
                        }
                    ],
                    'deleted'                 => Type::int(),
                    'leftovers'               => Type::int(),
                    'multiplicity'            => Type::int(),
                    'leftovers_salvi'            => Type::int(),
                    'leftovers_supplier'            => Type::int(),
                    'supplier_delivery_postpone'            => Type::int(),
                    'sort'                 => Type::int(),
                    'strict_multiplicity'                 => Type::boolean(),
                    'additional_assortment'                 => Type::boolean(),
                    'is_temperature_conditions'                 => Type::boolean(),
                    'is_season_goods'                 => Type::boolean(),
                    'discount_price'                 => Type::float(),
                    'discount_expire_date'                 => Type::int(),
                    'is_popular'                 => Type::boolean(),
//                    'thumb' => [
//                        'type' => Type::string(),
//                        'resolve' => function (Product $model) {
//                            if ($model->image) {
//                                return Yii::$app->urlManager->createAbsoluteUrl(['/product/thumb_' . $model->image->name]);
//                            }
//                            return Yii::$app->urlManager->createAbsoluteUrl(['/images/default.jpg']);
//                        }
//                    ]
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
