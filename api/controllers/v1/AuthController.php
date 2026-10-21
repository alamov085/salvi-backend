<?php

namespace api\controllers\v1;

use common\models\Chat;
use common\models\User;
use api\controllers\PublicController;
use api\models\forms\LoginForm;
use api\models\forms\RegisterForm;
use api\models\forms\PasswordResetRequestForm;
use api\models\forms\ResetPasswordForm;
use api\models\forms\VerifyEmailForm;
use Throwable;
use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class AuthController
 * @package api\controllers\v1
 */
class AuthController extends PublicController
{
    /**
     * @return array|LoginForm
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            /** @var  $user  User */
            return $this->userAccessToken();
        } else {
            $model->validate();
            return $model;
        }
    }

    /**
     * @return array
     * @throws Throwable
     */
    private function userAccessToken()
    {
        $userObject = User::find()->where(['user.id' => Yii::$app->user->getId()])
            ->joinWith(['costCenters', 'locations', 'company'])->one();
        $user = ArrayHelper::toArray($userObject);
        $user['company_name'] = ArrayHelper::getValue($userObject->company, 'short_name', '');

        $result = [
            'access_token' => Yii::$app->user->identity->getAuthKey(),
            'user' => $user,
            'permissions' => Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->getId()),
            'current_chat' => [
                'user_id' => Yii::$app->user->getId(),
                'chat_id' => Chat::activeChat()
            ]
        ];
        return $this->successResponse($result);
    }

    /**
     * @return array
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return ['success' => true];
    }

    /**
     * @return PasswordResetRequestForm|array
     * @throws InvalidConfigException
     */
    public function actionRecoveryPassword()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->sendEmail()) {
            return $this->successResponse();
        }
        $model->validate();
        return $model;
    }

    /**
     * @param int $type
     * @return RegisterForm|array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionRegister($type = 2)
    {
        $model = new RegisterForm();
        if ($type == 1) {
            $model->scenario = 'fop';
        } else {
            $model->scenario = 'ur';
        }
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') &&
            $model->validate() &&
            $model->register()) {
            return $this->successResponse();
        }
        $model->validate();
        return $model;
    }

    /**
     * @param $token
     * @return array
     * @throws Throwable
     */
    public function actionVerifyEmail($token = '')
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            return $this->failResponse([self::MESSAGE => $e->getMessage()]);
        }
        $user = $model->verifyEmail();
        if ($user &&
            Yii::$app->user->login($user)) {
            return $this->userAccessToken();
        }
        return $this->failResponse([
            self::MESSAGE => 'Sorry, we are unable to verify your account with provided token.'
        ]);
    }

    /**
     * @param string $token
     * @return ResetPasswordForm|array
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function actionResetPassword($token = '')
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            return $this->failResponse([self::MESSAGE => $e->getMessage()]);
        }

        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->validate()) {
            $result = $model->resetPassword();
            if ($result) {
                $userObject = User::find()->where(['user.id' => $result->id])
                    ->joinWith(['costCenters', 'locations', 'company'])->one();
                $user = ArrayHelper::toArray($userObject);
                $user['company_name'] = ArrayHelper::getValue($userObject->company, 'short_name', '');

                $result = [
                    'access_token' => $userObject->getAuthKey(),
                    'user' => $user,
                    'permissions' => Yii::$app->authManager->getPermissionsByUser($result->id),
                    'current_chat' => [
                        'user_id' => $result->id,
                        'chat_id' => Chat::activeChat()
                    ],
                    self::MESSAGE => 'Пароль успешно изменен'
                ];
                return $this->successResponse($result);
            }
        }

        $model->validate();
        return $model;
    }
}
