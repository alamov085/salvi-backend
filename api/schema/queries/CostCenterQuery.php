<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 15:05
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\CostCenter;
use common\models\Locations;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CostCenterQuery
 * @package api\schema\queries
 */
class CostCenterQuery extends BasicQuery
{
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
            if (Yii::$app->user->can('viewCostCenter')) {
                $query = CostCenter::find()->joinWith(['location', 'history']);
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(CostCenter::getTableSchema()->columns),
                    [Locations::class]
                );
                /** @var  $user User */
                $user = Yii::$app->user->getIdentity();
                if ($user->company_id) {
                    $query->where(['locations.company_id' => $user->company_id]);
                }
                if (isset($arguments['where'])) {
                    foreach ($arguments['where'] as $where) {
                        $query->andWhere($where);
                    }
                }
                if (!empty($args['search'])) {
                    $query->andWhere([
                        'or',
                        ['like', 'locations.name', $args['search']],
                        ['like', 'cost_center.name', $args['search']]
                    ]);
                }
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

    public function type()
    {
        return Types::costCenterPagination();
    }
}
