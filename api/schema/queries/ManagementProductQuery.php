<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 06.02.2020
 * Time: 14:45
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Category;
use common\models\Product;
use common\models\ProductBrand;
use common\models\ProductCategory;
use common\models\Unit;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ManagementProductQuery
 * @package api\schema\queries
 */
class ManagementProductQuery extends BasicQuery
{

    public function type()
    {
        return Types::managementProductPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'is_buffer'         => [
                'type' => Type::boolean(),
            ],
            'deleted'         => [
                'type' => Type::boolean(),
            ],
            'search'            => [
                'type' => Type::string()
            ],
            'empty_sku'         => [
                'type' => Type::boolean()
            ],
            'empty_uktzed'      => [
                'type' => Type::boolean()
            ],
            'empty_description' => [
                'type' => Type::boolean()
            ],
            'empty_vendor'      => [
                'type' => Type::boolean()
            ],
            'empty_brand'       => [
                'type' => Type::boolean()
            ],
            'empty_photo'       => [
                'type' => Type::boolean()
            ],
            'category_uuid'     => [
                'type' => Type::string()
            ],
            'empty_price'       => [
                'type' => Type::boolean()
            ],
            'empty_category'    => [
                'type' => Type::boolean()
            ],
            'empty_unit'        => [
                'type' => Type::boolean()
            ],
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (Yii::$app->user->can('managementProductView')) {
                $productIds = [];
                if (isset($args['category_uuid']) && $args['category_uuid'] != '') {
                    $productIds = ProductCategory::find()
                        ->where(['category_uuid' => $args['category_uuid']])
                        ->asArray()
                        ->all();
                    $productIds = ArrayHelper::getColumn($productIds, 'product_uuid');
                }
                $query = Product::find()->joinWith([
                        'unit',
                        'categoriesNew',
                        'images',
                        'productBrand',
                    ])
                    ->groupBy('product.id');

                if ($productIds) {
                    $query->andWhere(['in', 'product.uuid', $productIds]);
                }
                $query->andWhere(['!=', 'product.deleted', Product::STATUS_HIDDEN]);
                if (isset($args['is_buffer']) && $args['is_buffer']) {
                    $query->andWhere(['product.is_buffer' => 1]);
                } else {
                    $query->andWhere(['product.is_buffer' => 0]);
                }
                $query->andWhere('CASE WHEN `product_category`.`is_main`=1 THEN `product_category`.`is_main` = 1 ELSE `product_category`.`is_main` is null END');

                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Product::getTableSchema()->columns),
                    [Unit::class, Category::class, ProductBrand::class]
                );


                if (!empty($args['search'])) {
                    $query->andWhere([
                        'or',
                        ['like', 'product.name', $args['search']],
                        ['like', 'product.sku', $args['search']],
                        ['like', 'product.uuid', $args['search']],
                        ['like', 'product.buy_price', $args['search']],
                    ]);
                }
                if (isset($args['empty_sku']) && $args['empty_sku']) {
                    $query->andWhere([
                        'or',
                        ['product.sku' => null],
                        ['product.sku' => ''],
                    ]);
                }
                if (isset($args['empty_uktzed']) && $args['empty_uktzed']) {
                    $query->andWhere([
                        'or',
                        ['product.uktzed' => null],
                        ['product.uktzed' => ''],
                    ]);
                }
                if (isset($args['empty_description']) && $args['empty_description']) {
                    $query->andWhere([
                        'or',
                        ['product.description' => null],
                        ['product.description' => ''],
                    ]);
                }
                if (isset($args['empty_vendor']) && $args['empty_vendor']) {
                    $query->andWhere([
                        'or',
                        ['product.vendor_id' => null],
                        ['product.vendor_id' => ''],
                    ]);
                }
                if (isset($args['empty_brand']) && $args['empty_brand']) {
                    $query->andWhere([
                        'or',
                        ['product.brand_id' => null],
                        ['product.brand_id' => ''],
                    ]);
                }
                if (isset($args['empty_photo']) && $args['empty_photo']) {
                    $query->andWhere(['product_photo.id' => null]);
                }
                if (isset($args['empty_price']) && $args['empty_price']) {
                    $query->andWhere(['product.buy_price' => 0]);
                }
                if (isset($args['empty_unit']) && $args['empty_unit']) {
                    $query->andWhere(['unit.id' => null]);
                }
                if (isset($args['empty_category']) && $args['empty_category']) {
                    $query->andWhere(['product_category.id' => null]);
                }
                $total = $query->count();

                $query->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);

                $result = $query->all();

                return [
                    'model' => $result,
                    'total' => $total,
                ];
            } else {
                return self::accessDeniedPagination();
            }
        };
    }
}
