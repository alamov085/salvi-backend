<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 12.07.2019
 * Time: 17:53
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Contract;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ContractQuery
 * @package api\schema\queries
 */
class ContractQuery extends BasicQuery
{

    public function type()
    {
        return Types::contractPagination();
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
            if (Yii::$app->user->can('viewContract')) {

                /** @var  $user User */
                $user = Yii::$app->user->getIdentity();
                $query = Contract::find()->joinWith(['companyEntity', 'supplier', 'history']);
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Contract::getTableSchema()->columns)
                );

                $query->where(['contract.company_id' => $user->company_id]);
                if (!empty($args['search'])) {
                    $query->andWhere([
                        'or',
                        ['like', 'contract.name', $args['search']],
                        ['like', 'contract.number', $args['search']]
                    ]);
                }
                $query->groupBy('contract.id');
                $total = $query->count();

                $query->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
                return [
                    'model' => $query->all(),
                    'total' => $total,
                ];
            } else {
                return self::accessDeniedPagination();
            }
        };
    }
}
