<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2019
 * Time: 11:30
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\BudgetSpecification;
use common\models\CatalogLimitation;
use common\models\Category;
use common\models\CategoryExclude;
use common\models\Company;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\SearchQuery;
use common\models\Specification;
use common\models\SpecificationProduct;
use common\models\Unit;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ProductQuery
 * @package api\schema\queries
 */
class ProductQuery extends BasicQuery
{
    /** @var User */
    private $user;
    /** @var int */
    private $location_id = 0;

    public function __construct()
    {
        $this->user = Yii::$app->user->getIdentity();
    }

    public function type()
    {
        return Types::productPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id'                 => [
                'type' => Type::int(),
            ],
            'category_uuid'      => [
                'type' => Type::string()
            ],
            'all_products'       => [
                'type' => Type::boolean()
            ],
            'order_id'           => [
                'type' => Type::int()
            ],
            'location_id'        => [
                'type' => Type::int()
            ],
            'add_product'        => [
                'type' => Type::boolean()
            ],
            'specification_only' => [
                'type' => Type::boolean()
            ],
            'deleted' => [
                'type' => Type::int()
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (Yii::$app->user->can('viewProduct') || Yii::$app->user->can('viewCatalog')) {
                $allProducts = $this->allProducts($args);
                $productIds = $this->productIds($args);
                $specifications = $this->specifications($args);
                $specificationProductIds = $this->specificationProductIds($specifications);
                $excludeCategory = $this->excludeCategory();
                $productExist = $this->productExist($excludeCategory);

                $location_id = $this->location_id;
                $query = Product::find()
                    ->joinWith([
                        'unit',
                        'categories',
                        'image',
                        'specificationsProduct' => function ($query) use ($specifications, $location_id) {
                            if ($specifications && $location_id) {
                                $query->onCondition([
                                    'in', 'specification_product.specification_id', ArrayHelper::getColumn($specifications, 'id')
                                ]);
                            } else {
                                $query->onCondition([
                                    'specification_product.specification_id' => $location_id
                                ]);
                            }
                        }
                    ])
                    ->groupBy('product.id');

                if ($allProducts) {
                    if (isset($args['add_product'])) {
                        $add_product = $args['add_product'];
                    } else {
                        $add_product = false;
                    }
                    if (isset($args['category_uuid']) && $args['category_uuid'] != '') {
                        $query->andWhere(['in', 'product.uuid', $productIds]);
                    }
                    if (!$add_product && Yii::$app->user->can('catalogShowBtn') ||
                        $add_product && Yii::$app->user->can('orderShowBtn')) {
                        if (isset($budgetSpecification) && $budgetSpecification) {
                            $query->andWhere(['in', 'product.uuid', $specificationProductIds]);
                        } else {
                            if (isset($args['specification_only']) && $args['specification_only']) {
                                $query->andWhere(['in', 'product.uuid', $specificationProductIds]);
                            } else {
                                $query->andWhere([
                                    'or',
                                    ['in', 'product.uuid', ArrayHelper::getColumn($productExist, 'product_uuid')],
                                    ['in', 'product.uuid', $specificationProductIds]
                                ]);
                            }
                        }
                    } else {
                        $query->andWhere(['in', 'product.uuid', $specificationProductIds]);
                    }
                } else {
                    if (isset($args['category_uuid']) && $args['category_uuid'] != '') {
                        $query->andWhere(['in', 'product.uuid', $productIds]);
                    }
                }


                $query->andWhere(['!=', 'product.deleted', Product::STATUS_HIDDEN]);
                $query->andWhere(['product.is_buffer' => 0]);
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Product::getTableSchema()->columns),
                    [Unit::class, Category::class]
                );
                if (!empty($args['search'])) {
                    $query->andWhere([
                        'or',
                        ['like', 'product.name', $args['search']],
                        ['like', 'product.sku', $args['search']],
                    ]);

                    $searchQuery = new SearchQuery();
                    $searchQuery->query_string = $args['search'];
                    $searchQuery->created_at = time();

                    $searchQuery->save();


                }
                $total = $query->count();

                $query->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->orderBy([
//                        'deleted' => 'ASC',
                        $arguments['orderBy'] => $arguments['order'],
                    ]);
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

    /**
     * @param array $args
     * @return bool
     */
    private function allProducts(array $args): bool
    {
        if (isset($args['all_products'])) {
            return false;
        }
        return true;
    }

    /**
     * @param array $args
     * @return array
     */
    private function productIds(array $args): array
    {
        if (isset($args['category_uuid']) && $args['category_uuid'] != '') {
            $productIds = ProductCategory::find()
                ->where(['category_uuid' => $args['category_uuid']])
                ->asArray()
                ->all();
            return ArrayHelper::getColumn($productIds, 'product_uuid');
        }
        return [];
    }

    /**
     * @param array $args
     * @return array|Specification[]|\yii\db\ActiveRecord[]
     */
    private function specifications(array $args)
    {
        $specificationsQuery = Specification::find()
            ->joinWith(['locations'])
            ->where(['specification.company_id' => $this->user->company_id])
            ->active();
        if (isset($args['order_id']) && $args['order_id'] > 0) {
            $order = Order::find()->where(['id' => $args['order_id']])->one();
            if ($order) {
                $budgetSpecification = BudgetSpecification::find()
                    ->where(['budget_id' => $order->budget_id])
                    ->all();
                $this->location_id = $order->location_id;
                if ($budgetSpecification) {
                    $specificationsQuery->andWhere([
                        'in',
                        'specification.id',
                        ArrayHelper::getColumn($budgetSpecification, 'specification_id')
                    ]);
                }
            }
        }
        if (isset($args['location_id']) && $args['location_id'] > 0) {
            $this->location_id = $args['location_id'];
        }
        if (isset($args['location_id']) && $args['location_id'] > 0 && isset($order) && $order) {
            $specificationsQuery->andWhere(['specification_location.location_id' => $args['location_id']]);
        } elseif (isset($order) && $order) {
            $specificationsQuery->andWhere(['specification_location.location_id' => $order->location_id]);
        } elseif (isset($args['location_id']) && $args['location_id'] > 0) {
            $specificationsQuery->andWhere(['specification_location.location_id' => $args['location_id']]);
        }
        return $specificationsQuery->all();
    }

    /**
     * @param $specifications
     * @return array
     */
    private function specificationProductIds($specifications): array
    {
        if ($specifications) {
            $products = SpecificationProduct::find()
                ->select(['product_uuid'])
                ->where(['in', 'specification_id', ArrayHelper::getColumn($specifications, 'id')])
                ->asArray()
                ->all();
            if ($products) {
                return ArrayHelper::getColumn($products, 'product_uuid');
            }
        }
        return [];
    }

    /**
     * @return array
     */
    private function excludeCategory(): array
    {
        $excludeCategory = CategoryExclude::find()
            ->select(['category_uuid'])
            ->where(['company_id' => $this->user->company_id])
            ->asArray()
            ->all();
        if ($excludeCategory) {
            return ArrayHelper::getColumn($excludeCategory, 'category_uuid');
        }
        return [];
    }

    /**
     * @param array $excludeCategory
     * @return array
     */
    private function productExist(array $excludeCategory): array
    {
        return ProductCategory::find()
            ->select(['product_uuid'])
            ->where(['not in', 'category_uuid', $excludeCategory])
            ->groupBy('product_uuid')
            ->asArray()
            ->all();
    }
}
