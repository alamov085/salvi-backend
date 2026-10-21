<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.06.2019
 * Time: 14:38
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\NotificationHelper;
use api\models\forms\CreateLocationForm;
use common\models\CompanyEntity;
use common\models\CompanyEntityLocation;
use common\models\CostCenter;
use common\models\Locations;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class LocationsController
 * @package api\controllers\v1
 */
class LocationsController extends RestController
{
    public function actionCreateLocation()
    {
        if (Yii::$app->user->can('createLocations')) {
            $model = new CreateLocationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->createLocation()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    public function actionUpdateLocation()
    {
        if (Yii::$app->user->can('updateLocations')) {
            $model = new CreateLocationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->updateLocation()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    public function actionGetList()
    {
        if (Yii::$app->user->can('viewLocations')) {
            $companyEntity = CompanyEntity::find()->all();
            $query = Locations::find()->joinWith(['companyEntities', 'costCenters']);
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            if ($user->company_id) {
                $query->where(['in', 'locations.company_id', $user->companyList]);
            }
            $model = $query->offset($this->page * $this->perPage)
                ->limit($this->perPage)
                ->asArray()
                ->all();
            foreach ($model as $k => $item) {
                $model[$k]['companyEntitiesIds'] = ArrayHelper::getColumn(
                    $item['companyEntities'],
                    function ($element) {
                        return (int)$element['company_id'];
                    }
                );
            }
            $result = new ArrayDataProvider([
                'models' => $model,
                'pagination' => [
                    'pageSize' => $this->perPage
                ],
                'sort' => [
                    'attributes' => [
                        'id'
                    ]
                ]
            ]);
            return $this->vueTableResponse($result, ['company' => $companyEntity]);
        } else {
            return $this->accessDenied();
        }
    }

    /**
     * @return array
     */
    public function actionGetListOfCostCenters()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $id = ArrayHelper::getValue($this->post, 'id', []);
        $location = Locations::find()->where(['in', 'id', $id])->all();
        if ($location) {
            $costCentersIds = [];
            foreach ($location as $item) {
                $costCentersIds = array_merge(
                    $costCentersIds,
                    ArrayHelper::getColumn(ArrayHelper::getValue($item, 'costCenters'), 'id')
                );
            }
            $query = CostCenter::find()->where(['in', 'id', $costCentersIds]);
            if (Yii::$app->user->can('viewAndCreateAllOrder')) {
                $query->andWhere(['>', 'id', 0]);
            } elseif (Yii::$app->user->can('viewAndCreateLocationOrder')) {
                if ($user->all_location) {
                    $companyLocation = Locations::find()
                        ->where(['company_id' => $user->company_id])
                        ->all();
                    $locations = ArrayHelper::getColumn($companyLocation, 'id');
                } else {
                    $locations = ArrayHelper::getColumn($user->locations, 'location_id');
                }
                $costs = CostCenter::find()
                    ->where(['in', 'location_id', $locations])
                    ->all();
                $query->andWhere(['in', 'id', ArrayHelper::getColumn($costs, 'id')]);
            } elseif (Yii::$app->user->can('viewAndCreateCostOrder')) {
                $query->andWhere([
                    'in',
                    'id',
                    ArrayHelper::getValue($user, 'allCost', [])
                ]);
            } else {
                $query->andWhere([
                    'in',
                    'id',
                    ArrayHelper::getValue($user, 'allCost', [])
                ]);
            }
            $costCenters = $query->all();
            return $this->successResponse(['costCenters' => $costCenters]);
        }
        return $this->successResponse(['costCenters' => []]);
    }

    public function actionGetListOfCostCentersContract()
    {
        $id = ArrayHelper::getValue($this->post, 'id', []);
        $all_location = ArrayHelper::getValue($this->post, 'all_location', false);
        if ($all_location) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            if ($user->all_location) {
                $companyLocation = Locations::find()
                    ->where(['company_id' => $user->company_id])
                    ->all();
                $locations = ArrayHelper::getColumn($companyLocation, 'id');
            } else {
                $locations = ArrayHelper::getColumn($user->locations, 'location_id');
            }
            $location = Locations::find()->where(['in', 'id', $locations])->all();
        } else {
            $location = Locations::find()->where(['in', 'id', $id])->all();
        }
        if ($location) {
            $costCentersIds = [];
            foreach ($location as $item) {
                $costCentersIds = array_merge(
                    $costCentersIds,
                    ArrayHelper::getColumn(ArrayHelper::getValue($item, 'costCenters'), 'id')
                );
            }
            $query = CostCenter::find()->where(['in', 'id', $costCentersIds]);
            $costCenters = $query->all();
            return $this->successResponse(['costCenters' => $costCenters]);
        }
        return $this->successResponse(['costCenters' => []]);
    }


    /**
     * @return array
     * @throws \Throwable
     */
    public function actionChangeStatus()
    {
        if (Yii::$app->user->can('statusLocations')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $status = ArrayHelper::getValue($this->post, 'status', 0);
            $model = Locations::find()->where(['id' => $id])->one();
            if ($model != false && $model->changeStatus($status)) {
                NotificationHelper::changeStatusLocation($model, $status);
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionGetCompanyEntity()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $location_id = ArrayHelper::getValue($this->post, 'id', 0);
        $result = [];
        if ($location_id) {
            $location = Locations::find()->where(['id' => $location_id])->andWhere(['allCompanies' => 1])->one();
            $companyEntity = CompanyEntityLocation::find()
                ->where(['location_id' => $location_id])
                ->all();
            if ($companyEntity || $location) {
                $query = CompanyEntity::find()->select(['id', 'short_name']);
                $query->where(['company_id' => $user->company_id]);
                if ($companyEntity) {
                    $query->andWhere(['in', 'id', ArrayHelper::getColumn($companyEntity, 'company_id')]);
                }
                $result = $query->asArray()->all();
                if ($result) {
                    foreach ($result as $k => $val) {
                        $result[$k]['id'] = +$val['id'];
                    }
                }
            }
        }
        return $this->successResponse(['companyEntity' => $result]);
    }
}
