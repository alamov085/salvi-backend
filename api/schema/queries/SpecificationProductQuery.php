<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 18:49
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Specification;
use common\models\SpecificationProduct;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class SpecificationProductQuery
 * @package api\schema\queries
 */
class SpecificationProductQuery extends BasicQuery
{

    public function type()
    {
        return Types::specificationProductPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'specification_id' => [
                'type' => Type::nonNull(Type::int()),
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (Yii::$app->user->can('viewSpecification')) {
                $query = SpecificationProduct::find()
                    ->select('specification_product.*,
                    (100+`specification_product`.`price`)/100*`product`.`buy_price` as mprice')
                    ->joinWith(['product', 'product.unit'])
                    ->where(['specification_product.specification_id' => $args['specification_id']])
                    ->andWhere(['!=', 'product.deleted', 2]);
                if (!empty($args['search'])) {
                    $query->andWhere([
                        'or',
                        ['like', 'product.name', $args['search']],
                        ['like', 'product.sku', $args['search']],
                    ]);
                }
                $query->groupBy('specification_product.id');
                $total = $query->count();
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Specification::getTableSchema()->columns)
                );
                if ($args['orderBy'] == 'null') {
                    $orderBy = 'specification_product.id';
                } else {
                    $orderBy = $args['orderBy'];
                }
                $query->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->orderBy($orderBy . ' ' . $arguments['order']);
                return [
                    'model' => $query->all(),
                    'total' => $total,
                ];
            }
            return self::accessDeniedPagination();
        };
    }
}
