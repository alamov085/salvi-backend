<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.08.2019
 * Time: 16:25
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Contract;
use common\models\Locations;
use common\models\Specification;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class SpecificationQuery
 * @package api\schema\queries
 */
class SpecificationQuery extends BasicQuery
{

    public function type()
    {
        return Types::specificationPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ],
            'list' => [
                'type' => Type::boolean()
            ],
            'active_only' => [
                'type' => Type::boolean()
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            if (isset($args['list'])) {
                $list = $args['list'];
            } else {
                $list = false;
            }
            if (Yii::$app->user->can('viewSpecification') || $list) {
                /** @var  $user User */
                $user = Yii::$app->user->getIdentity();
                $query = Specification::find()
                    ->joinWith(['locations.currentLocation', 'contract', 'history'])
                    ->where(['specification.company_id' => $user->company_id]);

                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(Specification::getTableSchema()->columns),
                    [Locations::class, Contract::class]
                );
                if (!empty($args['search'])) {
                    $query->andWhere([
                        'or',
                        ['like', 'specification.name', $args['search']],
                        ['like', 'contract.name', $args['search']],
                        ['like', 'locations.name', $args['search']],
                    ]);
                }
                if (isset($args['active_only']) && $args['active_only']) {
                    $query->active();
                }
                $query->groupBy('specification.id');
                $total = $query->count();
                $query->offset($arguments['perPage'] * $arguments['page'])
                    ->limit($arguments['perPage'])
                    ->orderBy($arguments['orderBy'] . ' ' . $arguments['order']);
                return [
                    'model' => $query->all(),
                    'total' => $total,
                ];
            }
            return self::accessDeniedPagination();
        };
    }
}
