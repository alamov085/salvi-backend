<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 17.06.2019
 * Time: 14:38
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\CreateCostCenterForm;
use api\models\forms\CreateLocationForm;
use common\models\CompanyEntity;
use common\models\CostCenter;
use common\models\Locations;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class CostCentersController
 * @package api\controllers\v1
 */
class CostCentersController extends RestController
{
    public function actionCreate()
    {
        if (Yii::$app->user->can('createCostCenter')) {
            $model = new CreateCostCenterForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->createCostCenter()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateCostCenter')) {
            $model = new CreateCostCenterForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->updateCostCenter()) {
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
        if (Yii::$app->user->can('viewCostCenter')) {
            $queryCompany = Locations::find();
            $query = CostCenter::find()->joinWith(['location']);
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            if ($user->company_id) {
                $query->where(['in', 'locations.company_id', $user->companyList]);
                $queryCompany->where(['in', 'company_id', $user->companyList]);
            }
            $companies = $queryCompany->all();
            $model = $query->offset($this->page * $this->perPage)
                ->limit($this->perPage)
                ->asArray()
                ->all();
            foreach ($model as $k => $value) {
                $model[$k]['location_id'] = (int)$value['location_id'];
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
            return $this->vueTableResponse($result, ['companies' => $companies]);
        } else {
            return $this->accessDenied();
        }
    }

    public function actionGetListByLocation()
    {
        $ids = Yii::$app->request->post('location');
        $all_location = ArrayHelper::getValue($this->post, 'all_location', false);
        if ($all_location) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $locations = Locations::find()
                ->where(['company_id' => $user->company_id])
                ->asArray()
                ->all();
            if ($locations) {
                $ids = ArrayHelper::getColumn($locations, 'id');
            } else {
                $ids = [];
            }
        }

        if (empty($ids)) {
            return $this->successResponse(['data' => []]);
        }
        $query = CostCenter::find();
        $result = $query->where(['in', 'location_id', $ids])
            ->all();
        return $this->successResponse(['data' => $result]);
    }
}
