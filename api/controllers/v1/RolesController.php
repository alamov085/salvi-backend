<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.07.2019
 * Time: 17:08
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\NotificationHelper;
use api\models\forms\RolesForm;
use common\models\AuthItem;
use common\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\helpers\ArrayHelper;

/**
 * Class RolesController
 * @package api\controllers\v1
 */
class RolesController extends RestController
{
    /**
     * @return RolesForm|array
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        if (Yii::$app->user->can('createRoles')) {
            $model = new RolesForm();
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
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        if (Yii::$app->user->can('updateRoles') && $user->company_id ||
            Yii::$app->user->can('updateManagerRoles') && !$user->company_id) {
            $model = new RolesForm();
            if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate() &&
                $model->update()) {
                return $this->successResponse();
            }
            $model->validate();
            return $model;
        }
        return $this->accessDenied();
    }

    public function actionDeactivate()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        if (Yii::$app->user->can('changeStatusRoles') && $user->company_id ||
            Yii::$app->user->can('changeStatusManagerRoles') && !$user->company_id) {
            $roleName = ArrayHelper::getValue($this->post, 'role_name');
            try {
                Yii::$app
                    ->db
                    ->createCommand()
                    ->update(AuthItem::tableName(), ['status' => 0], ['name' => $roleName])
                    ->execute();
            } catch (Exception $e) {
                return $this->failResponse();
            }
            $auth = Yii::$app->authManager;
            $defaultRole = $auth->getRole('defaultUser');
            $userIds = $auth->getUserIdsByRole($roleName);
            if (count($userIds)) {
                foreach ($userIds as $id) {
                    $auth->revokeAll($id);
                    $auth->assign($defaultRole, $id);
                }
                NotificationHelper::deactivateRole($userIds);
            }
            $roleName = AuthItem::find()->where(['name' => $roleName])->one();
            if ($roleName) {
                if ($user->company_id) {
                    NotificationHelper::changeStatusUserRoleManager($roleName->description, 0, $user->company_id);
                } else {
                    NotificationHelper::changeStatusManagerRoleManager($roleName->description, 0, $userIds);
                }
            }
            return $this->successResponse();

        }
        return $this->accessDenied();
    }

    public function actionActivate()
    {
        if (Yii::$app->user->can('changeStatusRoles')) {
            $roleName = ArrayHelper::getValue($this->post, 'role_name');
            $userIds = [];
            try {
                Yii::$app
                    ->db
                    ->createCommand()
                    ->update(AuthItem::tableName(), ['status' => 1], ['name' => $roleName])
                    ->execute();
                $users = User::find()->where(['old_role' => $roleName])->asArray()->all();
                if (!empty($users)) {
                    $auth = Yii::$app->authManager;
                    $role = $auth->getRole($roleName);
                    $userIds = ArrayHelper::getColumn($users, 'id');
                    foreach ($userIds as $id) {
                        $auth->revokeAll($id);
                        $auth->assign($role, $id);
                    }
                    NotificationHelper::activateRole($userIds);
                }
            } catch (Exception $e) {
                return $this->failResponse();
            }
            /** @var  $user User */
            $user = Yii::$app->user->getIdentity();
            $roleName = AuthItem::find()->where(['name' => $roleName])->one();
            if ($roleName) {
                if ($user->company_id) {
                    NotificationHelper::changeStatusUserRoleManager($roleName->description, 1, $user->company_id);
                } else {
                    NotificationHelper::changeStatusManagerRoleManager($roleName->description, 1, $userIds);
                }
            }
            return $this->successResponse();

        }
        return $this->accessDenied();
    }
}
