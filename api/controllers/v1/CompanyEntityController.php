<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.06.2019
 * Time: 8:24
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\NotificationHelper;
use api\models\forms\CompanyEntityForm;
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
 * Class CompanyEntity
 * @package api\controllers\v1
 */
class CompanyEntityController extends RestController
{
    public function actionGetList()
    {
        $query = CompanyEntity::find();
        $statuses = CompanyEntity::statuses();
        if (Yii::$app->user->can('createLocations')) {
            $query->where(['company_id' => Yii::$app->request->post('id')]);
        } else {
            $query->where(['company_id' => Yii::$app->user->getIdentity()->company_id]);
        }
        $model = $query->all();
        $result = new ArrayDataProvider([
            'models' => $model,
            'pagination' => [
                'pageSize' => (Yii::$app->request->get('per-page')) ?: 5
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'short_name',
                    'status.id'
                ]
            ]
        ]);
        return $this->vueTableResponse($result, ['statuses' => $statuses]);
    }

    public function actionMakeAsMain()
    {
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        $model = CompanyEntity::find()->where(['id' => $id])->one();
        if ($model != false) {
            if ($model->makeAsMain()) {
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $this->failResponse(['message' => 'Company not found']);
    }

    public function actionCreate()
    {
        if (Yii::$app->user->can('createCompanyEntity')) {
            $model = new CompanyEntityForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                if (Yii::$app->authManager->getAssignment('admin', Yii::$app->user->getId())) {
                    $model->company_id = ArrayHelper::getValue($this->post, 'current_company', 0);
                } else {
                    $model->company_id = Yii::$app->user->getIdentity()->company_id;
                }
                if ($model->validate() && $model->create()) {
                    return $this->successResponse();
                }
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateCompanyEntity')) {
            $model = new CompanyEntityForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '')) {
                if (Yii::$app->authManager->getAssignment('admin', Yii::$app->user->getId())) {
                    $model->company_id = ArrayHelper::getValue($this->post, 'current_company', 0);
                } else {
                    $model->company_id = Yii::$app->user->getIdentity()->company_id;
                }
                if ($model->validate() && $model->update()) {
                    return $this->successResponse();
                }
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionChangeStatus()
    {
        if (Yii::$app->user->can('statusCompanyEntity')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $status = ArrayHelper::getValue($this->post, 'status', 0);
            $model = CompanyEntity::find()->where(['id' => $id])->one();
            if ($model != false && $model->changeStatus($status)) {
                if ($status == CompanyEntity::STATUS_ACTIVE) {
                    NotificationHelper::activatedCompanyEntity($model);
                } else {
                    NotificationHelper::deactivatedCompanyEntity($model);
                }
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionGetListOfLocations()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        $allLocations = ArrayHelper::getValue($this->post, 'all_location', false);
        $companyEntity = CompanyEntity::find()->where(['id' => $id])->one();
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
            $locations = $query->asArray()->all();
            if ($locations) {
                foreach ($locations as $k => $val) {
                    $locations[$k]['id'] = +$val['id'];
                }
            }
            return $this->successResponse(['locations' => $locations]);
        }
        return $this->successResponse(['locations' => []]);
    }

    public function actionGetListOfLocationsContract()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $id = ArrayHelper::getValue($this->post, 'id', 0);
        $companyEntity = CompanyEntity::find()->where(['id' => $id])->one();
        if ($companyEntity != false) {
            $ids = ArrayHelper::getColumn($companyEntity->companyEntityLocation, 'location_id');

            $query = Locations::find()
                ->where([
                    'or',
                    ['in', 'id', $ids],
                    ['allCompanies' => 1]
                ])
                ->andWhere(['isActive' => true])
                ->andWhere(['company_id' => $user->company_id]);
            $locations = $query->all();
            return $this->successResponse(['locations' => $locations]);
        }
        return $this->successResponse(['locations' => []]);
    }

}
