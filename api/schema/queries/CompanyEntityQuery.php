<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 23:51
 */

namespace api\schema\queries;

use api\schema\PaginationType;
use api\schema\Types;
use common\models\CompanyEntity;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CompanyEntityQuery
 * @package api\schema\queries
 */
class CompanyEntityQuery extends BasicQuery
{

    public function type()
    {
        return Types::companyEntityPagination();
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
            'order_filter' => [
                'type' => Type::boolean()
            ]
        ]);
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $query = CompanyEntity::find()->joinWith(['companyEntityLocation', 'history']);
            $arguments = PaginationType::arguments(
                $args,
                array_keys(CompanyEntity::getTableSchema()->columns)
            );
            $query->where(['company_entity.company_id' => Yii::$app->user->getIdentity()->company_id]);
            if (isset($args['status'])) {
                $query->andWhere(['company_entity.status' => $args['status']]);
            }
            if (!empty($args['search'])) {
                $query->andWhere([
                    'or',
                    ['like', 'company_entity.short_name', $args['search']],
                    ['like', 'company_entity.full_name', $args['search']]
                ]);
            }
            if (isset($args['order_filter']) && $args['order_filter']) {
                if (Yii::$app->user->can('viewAndCreateAllOrder')) {
                    $query->andWhere(['>', 'company_entity.id', 0]);
                } elseif (Yii::$app->user->can('viewAndCreateLocationOrder')) {
                    if ($user->all_location) {
                        $query->andWhere(['>', 'company_entity.id', 0]);
                    } else {
                        $locations = ArrayHelper::getColumn($user->locations, 'location_id');
                        $locationModel = Locations::find()
                            ->where(['in', 'id', $locations])
                            ->andWhere(['allCompanies' => 1])
                            ->exists();
                        if ($locationModel) {
                            $query->andWhere(['>', 'company_entity.id', 0]);
                        } else {
                            $companies = CompanyEntityLocation::find()
                                ->where(['in', 'location_id', $locations])
                                ->all();
                            if ($companies) {
                                $query->andWhere([
                                    'in',
                                    'company_entity.id',
                                    ArrayHelper::getColumn($companies, 'company_id')
                                ]);
                            }
                        }
                    }
                } elseif (Yii::$app->user->can('viewAndCreateCostOrder')) {
                    if ($user->all_location) {
                        $query->andWhere(['>', 'company_entity.id', 0]);
                    } else {
                        $cost = CostCenter::find()
                            ->where([
                                'in',
                                'id',
                                ArrayHelper::getValue($user, 'allCost', [])
                            ])
                            ->all();
                        if ($cost) {
                            $locationModel = Locations::find()
                                ->where(['in', 'id', ArrayHelper::getColumn($cost, 'location_id')])
                                ->andWhere(['allCompanies' => 1])
                                ->exists();
                            if ($locationModel) {
                                $query->andWhere(['>', 'company_entity.id', 0]);
                            } else {
                                $companies = CompanyEntityLocation::find()
                                    ->where(['in', 'location_id', ArrayHelper::getColumn($cost, 'location_id')])
                                    ->all();
                                if ($companies) {
                                    $query->andWhere([
                                        'in',
                                        'company_entity.id',
                                        ArrayHelper::getColumn($companies, 'company_id')
                                    ]);
                                }
                            }
                        } else {
                            $query->andWhere(['<', 'company_entity.id', 0]);
                        }
                    }
//                    $query->andWhere([
//                        'in',
//                        'order.cost_center_id',
//
//                    ]);
                }
            }
            $query->groupBy('company_entity.id');
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
