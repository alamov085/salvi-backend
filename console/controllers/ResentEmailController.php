<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 16.03.2020
 * Time: 16:44
 */

namespace console\controllers;

use api\helpers\UserEmailHelper;
use common\models\User;
use Yii;
use yii\console\Controller;

/**
 * Class ResentEmailController
 * @package console\controllers
 */
class ResentEmailController extends Controller
{
    public function actionIndex()
    {
        $users = User::find()
            ->where(['company_id' => 136])
            ->andWhere(['manager' => 0])
            ->andWhere(['!=', 'id', 1108])
            ->all();
        if ($users) {
            foreach ($users as $user) {
                $pass = Yii::$app->security->generateRandomString();
                $user->setPassword($pass);
                $user->generateAuthKey();
                $user->generateEmailVerificationToken();
                if ($user->save()) {
                    UserEmailHelper::createdUser($user, $pass);
                }
            }
        }
    }
}
