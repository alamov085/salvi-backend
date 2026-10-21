<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.02.2020
 * Time: 10:19
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\AdminEmailForm;
use common\models\AdminEmail;
use Yii;
use yii\helpers\ArrayHelper;

class AdminEmailController extends RestController
{
    /**
     * @return AdminEmailForm|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        if (Yii::$app->user->can('managementEmail')) {
            $model = new AdminEmailForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
                $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    /**
     * @return AdminEmailForm|array
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdate()
    {
        if (Yii::$app->user->can('managementEmail')) {
            $model = new AdminEmailForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
                $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        } else {
            return $this->accessDenied();
        }
    }

    public function actionDeactivate()
    {
        if (Yii::$app->user->can('managementEmail')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $model = AdminEmail::find()->where(['id' => $id])->one();
            if ($model) {
                $model->status = AdminEmail::STATUS_NOT_ACTIVE;
                if ($model->save()) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        } else {
            return $this->accessDenied();
        }
    }

    public function actionActivate()
    {
        if (Yii::$app->user->can('managementEmail')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $model = AdminEmail::find()->where(['id' => $id])->one();
            if ($model) {
                $model->status = AdminEmail::STATUS_ACTIVE;
                if ($model->save()) {
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        } else {
            return $this->accessDenied();
        }
    }

    public function actionDelete()
    {
        if (Yii::$app->user->can('managementEmail')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $model = AdminEmail::find()->where(['id' => $id])->one();
            if ($model && $model->delete()) {
                return $this->successResponse();
            }
            return $this->failResponse();
        } else {
            return $this->accessDenied();
        }
    }
}
