<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 01.07.2019
 * Time: 18:11
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\Budget;
use common\models\BudgetLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class BudgetQuery
 * @package api\schema\queries
 */
class BudgetQuery extends BasicQuery
{

    public function type()
    {
        return Types::budgetPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ],
            'from' => [
                'type' => Type::string()
            ],
            'to' => [
                'type' => Type::string()
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            if (isset($args['from'])) {
                $from = +$args['from'] / 1000;
                $to = +$args['to'] / 1000;
                if ($from == $to) {
                    $to += 86399;
                }
                $budgetIds = Budget::budgetsInPeriod($from, $to);
                \Yii::info($budgetIds);
            }
            $query = Budget::find()
                ->joinWith(['responsibleUser', 'costCenters', 'locationsData', 'locations as loc', 'history'])
                ->where(['in', 'budget.status', [Budget::STATUS_ACTIVE, Budget::STATUS_INACTIVE]])
                ->andWhere(['budget.company_id' => $user->company_id]);
            if (Yii::$app->user->can('budgetViewAll')) {
                $query->andWhere(['>', 'budget.id', 0]);
            } elseif (Yii::$app->user->can('budgetViewLocation')) {
                $ids = BudgetLocation::find()
                    ->where(['in', 'location_id', $user->allLocations])
                    ->all();
                if ($ids) {
                    $query->andWhere(['in', 'budget.id', ArrayHelper::getColumn($ids, 'budget_id')]);
                } else {
                    $query->andWhere(['budget.id' => 0]);
                }
            } elseif (Yii::$app->user->can('budgetViewCost')) {
                $costs = ArrayHelper::getValue($user, 'allCost', []);
                $locations = CostCenter::find()
                    ->where(['in', 'id', $costs])
                    ->all();
                if ($locations) {
                    $ids = BudgetLocation::find()
                        ->where([
                            'in',
                            'location_id',
                            ArrayHelper::getColumn($locations, 'location_id')
                        ])
                        ->all();
                    if ($ids) {
                        $query->andWhere(['in', 'budget.id', ArrayHelper::getColumn($ids, 'budget_id')]);
                    } else {
                        $query->andWhere(['budget.id' => 0]);
                    }
                } else {
                    $query->andWhere(['budget.id' => 0]);
                }
            }
            $orWhere = ['and',
                ['budget.user_id_responsible' => $user->id],
                ['in', 'budget.status', [Budget::STATUS_ACTIVE, Budget::STATUS_INACTIVE]],
                ['budget.company_id' => $user->company_id]
            ];
            if (!empty($args['search'])) {
                $query->andWhere(['like', 'budget.name', $args['search']]);
                $orWhere[] = ['like', 'budget.name', $args['search']];
            }
            $query->orWhere($orWhere);
            $arguments = PaginationType::arguments(
                $args,
                array_keys(Budget::getTableSchema()->columns)
            );

            if (isset($budgetIds)) {
                $query->andWhere(['in', 'budget.id', $budgetIds]);
            }
            $query->groupBy('budget.id');
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
