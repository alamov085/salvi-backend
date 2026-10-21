<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 26.06.2019
 * Time: 12:28
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\CompanyForm;
use api\models\forms\CreateLocationForm;
use api\models\forms\MyCompanyForm;
use api\models\forms\RegisterForm;
use api\models\forms\RegisterFormAdmin;
use common\models\Company;
use common\models\CompanyEntity;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class CompanyController
 * @package api\controllers\v1
 */
class CompanyController extends RestController
{
    public function actionGetList()
    {
        $query = Company::find();
        if (!Yii::$app->user->can('viewCompanyList')) {
            $query->where(['id' => Yii::$app->user->getIdentity()->company_id]);
        }
        $model = $query->asArray()->all();
        foreach ($model as $k => $value) {
            $model[$k]['show_catalog'] = (int)$value['show_catalog'];
        }
        $result = new ArrayDataProvider([
            'models' => $model,
            'pagination' => [
                'pageSize' => (Yii::$app->request->get('per-page')) ?: 5
            ],
            'sort' => [
                'attributes' => [
                    'id'
                ]
            ]
        ]);
        $statuses = Company::statuses();
        return $this->vueTableResponse($result, ['statuses' => $statuses]);
    }

    public function actionUpdate()
    {
        $model = new CompanyForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
            $model->update()) {
            return $this->successResponse();
        }
        $model->validate();
        return $model;
    }

    public function actionApproveRegistration()
    {
        $id = Yii::$app->request->post('id');
        $company = Company::find()->where(['id' => $id])->one();
        if ($company) {
            if ($company->approveRegistration()) {
                return $this->successResponse($company);
            }
            return $this->failResponse(['message' => 'Company was not approved']);
        }
        return $this->failResponse(['message' => 'Company not found']);
    }

    public function actionRefuseRegistration()
    {
        $id = Yii::$app->request->post('id');
        $reason = Yii::$app->request->post('reason');
        $company = Company::find()->where(['id' => $id])->one();
        if ($company) {
            if ($company->refuseRegistration($reason)) {
                return $this->successResponse($company);
            }
            return $this->failResponse(['message' => 'Something went wrong...']);
        }
        return $this->failResponse(['message' => 'Company not found']);
    }

    public function actionRollBackRegistration()
    {
        $id = Yii::$app->request->post('id');
        $company = Company::find()->where(['id' => $id])->one();
        if ($company) {
            if ($company->rollBackRegistration()) {
                return $this->successResponse($company);
            }
            return $this->failResponse(['message' => 'Company was not approved']);
        }
        return $this->failResponse(['message' => 'Company not found']);
    }

    public function actionUpdateMyCompany()
    {
        if (Yii::$app->user->can('settingsCompany')) {
            $model = new MyCompanyForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionCreate($type = 2)
    {
        if (Yii::$app->user->can('createCompany')) {
            $model = new RegisterFormAdmin();
            if ($type == 1) {
                $model->scenario = 'fop';
            } else {
                $model->scenario = 'ur';
            }
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->register()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }
}
