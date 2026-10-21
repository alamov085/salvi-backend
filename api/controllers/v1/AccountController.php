<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 24.05.2019
 * Time: 15:31
 */

namespace api\controllers\v1;

use api\controllers\RestController;
use api\helpers\UserEmailHelper;
use api\models\forms\UserForm;
use common\models\Chat;
use common\models\CostCenter;
use common\models\Currency;
use common\models\Locations;
use common\models\User;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class AccountController
 * @package api\controllers\v1
 *
 * @property mixed $user
 */
class AccountController extends RestController
{
    /**
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);
        unset($actions['view']);
        return $actions;
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function actionIndex()
    {
        $currency = Currency::find()->all();
        $locations = Locations::find()
            ->where(['company_id' => Yii::$app->user->getIdentity()->company_id])
            ->all();
        $costCenter = CostCenter::find()
            ->where(['in', 'location_id', ArrayHelper::getColumn($locations, 'id')])
            ->all();
        return $this->successResponse([
            'user' => $this->getUser(),
            'currency' => $currency,
            'location' => $locations,
            'cost_center' => $costCenter,
            'current_chat' => [
                'user_id' => Yii::$app->user->getId(),
                'chat_id' => Chat::activeChat()
            ]
        ]);
    }

    /**
     * @return array
     */
    private function getUser()
    {
        $userObject = User::find()->where(['user.id' => Yii::$app->user->getId()])
            ->joinWith(['costCenters', 'locations', 'company'])->one();
        $user = ArrayHelper::toArray($userObject);
        $user['cost_center'] = ArrayHelper::getValue($userObject, 'allCost', []);
        $user['locations'] = ArrayHelper::getColumn($userObject->locations, 'location_id');
        $user['company_name'] = ArrayHelper::getValue($userObject->company, 'short_name', '');
        return $user;
    }

    /**
     * @return UserForm|array
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function actionUpdate()
    {
        $model = new UserForm();
        $user = $this->getUser();
        $emailChanged = false;

        if($user['email'] != Yii::$app->getRequest()->getBodyParam('email')) {
            $exists = User::find()->where(['email' => Yii::$app->getRequest()->getBodyParam('email')])->exists();

            if($exists) {
                return $this->failResponse(['errors' => [
                        [
                            'field' => 'email',
                            'message' => 'Цей e-mail вже зайнятий іншим користувачем'
                        ]
                    ]
                ]);
            }


            $model->password = $model->randomPassword(8);
            $model->repeatPassword = $model->password;

            $emailChanged = true;
        }

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate()) {

            if($model->save()){
                if($emailChanged) {
                    UserEmailHelper::emailChangedUser(Yii::$app->getRequest()->getBodyParam('email'), $model->password);
                }
            }

            return $this->successResponse(['user' => $this->getUser(), 'emailChanged' => $emailChanged]);
        } else {
            $model->validate();
            return $model;
        }
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function actionSetCompany()
    {
        /** @var  $user User */
        $user = Yii::$app->user->getIdentity();
        if ($user && $user->manager) {
            $model = User::find()->where(['id' => Yii::$app->user->getId()])->one();
            if ($model) {
                $model->company_id = ArrayHelper::getValue($this->post, 'id', 0);
                if ($model->save()) {
                    return $this->successResponse(['user' => $this->getUser()]);
                }
            }
            return $this->failResponse();
        }
        return $this->accessDenied();
    }

    /**
     * @return array
     */
    public function actionPermissions()
    {
        $permissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->getId());
        return $this->successResponse([
            'permissions' => $permissions,
            'user' => $this->getUser()
        ]);
    }
}
