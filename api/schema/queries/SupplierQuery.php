<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.07.2019
 * Time: 14:12
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Supplier;
use common\models\User;
use GraphQL\Type\Definition\Type;
use yii\helpers\ArrayHelper;

/**
 * Class SupplierQuery
 * @package api\schema\queries
 */
class SupplierQuery extends BasicQuery
{

    public function type()
    {
        return Types::supplierPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = \Yii::$app->user->getIdentity();
            $query = Supplier::find()
                ->joinWith(['companySupplier', 'history']);
            if ($user->company_id) {
                $query->andWhere(['company_supplier.company_id' => $user->company_id]);
            }
            $arguments = PaginationType::arguments(
                $args,
                array_keys(Supplier::getTableSchema()->columns)
            );
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'supplier.name', $args['search']],
                    ['like', 'supplier.edrpou', $args['search']],
                    ['like', 'supplier.inn', $args['search']],
                    ['like', 'supplier.description', $args['search']],
                ]);
            }
            $query->groupBy('supplier.id');
            $total = $query->count();

            $query->offset($arguments['perPage'] * $arguments['page'])
                ->limit($arguments['perPage'])
                ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
            return [
                'model' => $query->all(),
                'total' => $total,
            ];
        };
    }
}
