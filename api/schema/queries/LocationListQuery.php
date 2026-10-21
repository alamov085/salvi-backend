<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.12.2019
 * Time: 15:10
 */

namespace api\schema\queries;

use api\schema\Types;
use common\models\CompanyEntity;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\Order;
use common\models\User;
use GraphQL\Type\Definition\Type;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class LocationListQuery
 * @package api\schema\queries
 */
class LocationListQuery extends BasicQuery
{

    public function type()
    {
        return Type::listOf(Types::locationList());
    }

    public function args()
    {
        return [
            'id' => Type::int(),
            'all_location' => Type::boolean(),
            'order_id' => Type::int()
        ];
    }

    public function resolve()
    {
        return function ($root, $args) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $id = ArrayHelper::getValue($args, 'id', 0);
            $allLocations = ArrayHelper::getValue($args, 'all_location', false);
            $orderId = ArrayHelper::getValue($args, 'order_id', 0);
            $companyEntity = CompanyEntity::find()->where(['id' => $id])->one();
            if ($orderId) {
                $order = Order::find()
                    ->joinWith(['budget.locations'])
                    ->where(['order.id' => $orderId])
                    ->one();
                if ($order && $order->budget && !$order->budget->all_location) {
                    $locationsId = ArrayHelper::getColumn($order->budget->locations, 'location_id');
                }
            }
            if ($allLocations) {
                $companyEntity = CompanyEntity::find()->where(['company_id' => $user->company_id])->all();
            }
            if ($companyEntity) {
                if ($allLocations) {
                    $allIds = CompanyEntityLocation::find()
                        ->where(['in', 'company_id', ArrayHelper::getColumn($companyEntity, 'id')])
                        ->all();
                    $ids = ArrayHelper::getColumn($allIds, 'location_id');
                } else {
                    $ids = ArrayHelper::getColumn($companyEntity->companyEntityLocation, 'location_id');
                }

                $query = Locations::find()
                    ->select(['id', 'name'])
                    ->where([
                        'or',
                        ['in', 'id', $ids],
                        ['allCompanies' => 1]
                    ])
                    ->andWhere(['isActive' => true])
                    ->andWhere(['company_id' => $user->company_id]);
                if (Yii::$app->user->can('viewAndCreateAllOrder')) {
                    //$query->andWhere(['>', 'id', 0]);
                } elseif (Yii::$app->user->can('viewAndCreateLocationOrder')) {
                    if ($user->all_location) {
                        $companyLocation = Locations::find()
                            ->where(['company_id' => $user->company_id])
                            ->all();
                        $locations = ArrayHelper::getColumn($companyLocation, 'id');
                    } else {
                        $locations = ArrayHelper::getColumn($user->locations, 'location_id');
                    }
                    $query->andWhere(['in', 'id', $locations]);
                } elseif (Yii::$app->user->can('viewAndCreateCostOrder')) {
                    $cost = CostCenter::find()
                        ->where(['in', 'id', ArrayHelper::getValue($user, 'allCost', [])])
                        ->all();
                    $query->andWhere([
                        'in',
                        'id',
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
                    $query->andWhere(['in', 'id', $locations]);
                }
                if (isset($locationsId)) {
                    //Yii::error($locationsId);
                    $query->andWhere(['in', 'id', $locationsId]);
                }
                
                $locations = $query->asArray()->all();
                return $locations;
            }
            return [];
        };
    }
}
