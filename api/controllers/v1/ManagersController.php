<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 03.07.2019
 * Time: 10:55
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\models\forms\CreateUserForm;
use api\models\forms\UserManagerForm;
use common\models\User;
use common\models\UserCompany;
use Exception;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class ManagersController
 * @package api\controllers\v1
 */
class ManagersController extends RestController
{
    public function actionCreate()
    {
        if (Yii::$app->user->can('createManager')) {
            $model = new UserManagerForm();
            $model->scenario = 'create';
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->create()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionUpdate()
    {
        if (Yii::$app->user->can('updateManager')) {
            $model = new UserManagerForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionRemoveFromCompany()
    {
        if (Yii::$app->user->can('updateManager')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $id = ArrayHelper::getValue($this->post, 'id', 0);
            UserCompany::deleteAll([
                'and',
                ['user_id' => $id],
                ['company_id' => $user->company_id]
            ]);
            return $this->successResponse();
        }
        return $this->accessDenied();
    }

    public function actionGetListToAdd()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        $notInList = UserCompany::find()
            ->where(['company_id' => $user->company_id])
            ->all();
        $notIn = ArrayHelper::getColumn($notInList, 'user_id');
        $managers = User::find()
            ->select(['id', 'Concat(first_name," ",last_name) as name '])
            ->where(['not in', 'id', $notIn])
            ->andWhere(['manager' => 1])
            ->andWhere(['all_company' => 0])
            ->asArray()
            ->all();
        return $this->successResponse(['managers' => $managers]);
    }

    /**
     * @return array
     * @throws \Throwable
     */
    public function actionAddCompany()
    {
        if (Yii::$app->user->can('createManager')) {
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $managers = ArrayHelper::getValue($this->post, 'manager', []);
            if (!empty($managers)) {
                $data = [];
                foreach ($managers as $manager) {
                    $data[] = [
                        'company_id' => $user->company_id,
                        'user_id' => $manager
                    ];
                }
                if (!empty($data)) {
                    try {
                        Yii::$app->db->createCommand()->batchInsert(
                            UserCompany::tableName(),
                            [
                                'company_id',
                                'user_id'
                            ],
                            $data
                        )->execute();
                    } catch (Exception $e) {
                        Yii::error($e);
                    }
                    return $this->successResponse();
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }
}
