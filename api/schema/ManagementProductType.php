<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2019
 * Time: 11:09
 */

namespace api\schema;

use api\helpers\NumberHelper;
use common\models\Product;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ManagementProductType
 * @package api\schema
 */
class ManagementProductType extends BasicType
{
    public function __construct($pagination = false)
    {
        $config = [
            'description' => 'Товары',
            'fields' => function () {
                return [
                    'id' => Type::int(),
                    'uuid' => Type::string(),
                    'sku' => Type::string(),
                    'uktzed' => Type::string(),
                    'buy_price' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            return NumberHelper::convert($model->buy_price, 2);
                        }
                    ],
                    'name' => Type::string(),
                    'description' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            return '' . $model->description;
                        }
                    ],
                    'unit_uuid' => Type::string(),
                    'unit_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            if ($model->unit) {
                                return $model->unit->name;
                            }
                            return "";
                        }
                    ],
                    'category_uuid' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            if ($model->categoriesNew) {
                                $result = ArrayHelper::index($model->categoriesNew, 'has_child');
                                return ArrayHelper::getValue($result, '0.uuid', '');
                            }
                            return '';
//                            return ArrayHelper::getValue($model->categories, '0.uuid');
                        }
                    ],
                    'category_type' => [
                        self::FIELD_TYPE => Type::int(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            if ($model->categoriesNew) {
                                $result = ArrayHelper::index($model->categoriesNew, 'has_child');
                                return ArrayHelper::getValue($result, '0.type', 0);
                            }
                            return 0;
//                            return ArrayHelper::getValue($model->categories, '0.type');
                        }
                    ],
                    'category_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $model) {
                            if ($model->categoriesNew) {
                                $result = ArrayHelper::index($model->categoriesNew, 'has_child');
                                return ArrayHelper::getValue($result, '0.name', '');
                            }
                            return '';
//                            return ArrayHelper::getValue($model->categories, '0.name');
                        }
                    ],
                    'images' => [
                        self::FIELD_TYPE => Type::listOf(Types::image()),
                        self::FIELD_RESOLVE => function (Product $model) {
                            return $model->images;
                        }
                    ],
                    'vendor_id' => Type::int(),
                    'brand_id' => Type::int(),
                    'brand_name' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $product) {
                            if ($product->productBrand) {
                                return $product->productBrand->name;
                            }
                            return '';
                        }
                    ],
                    'vat' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $product) {
                            return trim($product->vat);
                        }
                    ],
                    'props' => [
                        self::FIELD_TYPE => Type::string(),
                        self::FIELD_RESOLVE => function (Product $product) {
                            return '';
                        }
                    ],
                    'is_buffer' => Type::boolean(),
                    'buffer_status' => Type::int(),
                    'deleted' => Type::int()
                ];
            }
        ];
        parent::__construct($config, $pagination);
    }
}
