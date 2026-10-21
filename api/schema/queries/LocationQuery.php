<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 14:54
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
 * Class LocationQuery
 * @package api\schema\queries
 */
class LocationQuery extends BasicQuery
{
    public function type()
    {
        return Types::locationPagination();
    }

    public function args()
    {
        return ArrayHelper::merge(PaginationType::argsDescription(), [
            'id' => [
                'type' => Type::int(),
            ],
            'status' => [
                'type' => Type::int()
            ],
            'list' => [
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
            if (Yii::$app->user->can('viewLocations') || $list) {
                /** @var  $user User */
                $user = Yii::$app->user->getIdentity();
                $query = Locations::find()->joinWith(['costCenters', 'companyEntities', 'history'])
                    ->groupBy('locations.id');
                $query->where(['locations.company_id' => $user->company_id]);
                $arguments = PaginationType::arguments(
                    $args,
                    array_keys(CostCenter::getTableSchema()->columns)
                );
                if (!empty($args['search'])) {
                    $query->andWhere(['like', 'locations.name', $args['search']]);
                }
                if (!empty($args['status'])) {
                    $query->andWhere(['locations.isActive' => $args['status']]);
                }
                if ($list) {
                    if (Yii::$app->user->can('viewAndCreateAllOrder')) {
                        $query->andWhere(['>', 'locations.id', 0]);
                    } elseif (Yii::$app->user->can('viewAndCreateLocationOrder')) {
                        if ($user->all_location) {
                            $companyLocation = Locations::find()
                                ->where(['company_id' => $user->company_id])
                                ->all();
                            $locations = ArrayHelper::getColumn($companyLocation, 'id');
                        } else {
                            $locations = ArrayHelper::getColumn($user->locations, 'location_id');
                        }
                        $query->andWhere(['in', 'locations.id', $locations]);
                    } elseif (Yii::$app->user->can('viewAndCreateCostOrder')) {
                        $cost = CostCenter::find()
                            ->where(['in', 'id', ArrayHelper::getValue($user, 'allCost', [])])
                            ->all();
                        $query->andWhere([
                            'in',
                            'locations.id',
                            ArrayHelper::getColumn($cost, 'location_id')
                        ]);
                    } else {
                        if ($user->all_location) {
                            $companyLocation = Locations::find()
                                ->where(['company_id' => $user->company_id])
                                ->all();
                            $locations = ArrayHelper::getColumn($companyLocation, 'id');
                        } else {
                            $locations = ArrayHelper::getColumn($user->locations, 'location_id');
                        }
                        $query->andWhere(['in', 'locations.id', $locations]);
                    }
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
}
