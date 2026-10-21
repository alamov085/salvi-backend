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
use common\models\OrderProduct;
use common\models\Product;
use common\models\ProductCategory;
use common\models\Specification;
use common\models\SpecificationProduct;
use common\models\Unit;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

/**
 * Class AllProductQuery
 * @package api\schema\queries
 */
class AllProductQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::product());
    }

    public function args()
    {
        return [
            'search' => Type::nonNull(Type::string()),
            'orders' => Type::string()
        ];
    }

    public function resolve()
    {
        return function ($root, $args) {
            $query = Product::find()
                ->where([
                    'or',
                    ['like', 'product.name', $args['search']],
                    ['like', 'product.uuid', $args['search']],
                ]);
            if (isset($args['orders']) && $args['orders']) {
                $orders = explode(',', $args['orders']);
                $products = OrderProduct::find()
                    ->select(['product_uuid'])
                    ->where(['in', 'order_id', $orders])
                    ->groupBy('product_uuid')
                    ->asArray()
                    ->all();
                if ($products) {
                    $query->andWhere(['in', 'uuid', ArrayHelper::getColumn($products, 'product_uuid')]);
                } else {
                    return [];
                }
            }
            return $query->all();
        };
    }
}
