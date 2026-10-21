<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 16:52
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\NotificationHelper;
use api\models\forms\CoordinationForm;
use common\models\Coordination;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class CoordinationController
 * @package api\controllers\v1
 */
class CoordinationController extends RestController
{
    public function actionCreate()
    {
        if (Yii::$app->user->can('createCoordination')) {
            $model = new CoordinationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionUpdateOrder()
    {
        if (Yii::$app->user->can('createCoordination')) {
            $data = ArrayHelper::getValue($this->post, 'data');
            if (!empty($data)) {
                $id = 0;
                foreach ($data as $value) {
                    $model = Coordination::find()->where(['id' => $value['id']])->one();
                    if ($model != false) {
                        $model->order = $value['order'];
                        $model->save();
                        $id = $model->company_id;
                    }
                }
                NotificationHelper::changeCoordinationOrder($id);
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateCoordination')) {
            $model = new CoordinationForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionChangeStatus()
    {
        if (Yii::$app->user->can('changeStatusCoordination')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $status = ArrayHelper::getValue($this->post, 'status', 0);
            $model = Coordination::find()->where(['id' => $id])->one();
            if ($model != false && $model->changeStatus($status)) {
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    public function actionDelete()
    {
        if (Yii::$app->user->can('deleteCoordination')) {
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            $model = Coordination::find()->where(['id' => $id])->one();
            if ($model) {
                $model->delete();
                return $this->successResponse();
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }
}
