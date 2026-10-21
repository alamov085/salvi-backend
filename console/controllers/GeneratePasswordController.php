<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 28.10.2019
 * Time: 17:23
 */

namespace console\controllers;

use api\helpers\UniSender\UnisenderSended;
use api\helpers\UserEmailHelper;
use common\models\EmailLog;
use common\models\User;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class GeneratePasswordController
 * @package console\controllers
 */
class GeneratePasswordController extends Controller
{
    public function actionIndex()
    {
        $notIn = UnisenderSended::find()
            ->where(['like', 'message', 'пароль'])
            ->andWhere(['code' => 'success'])
            ->andWhere(['>=', 'id', 15709])
            ->andWhere(['<=', 'id', 15738])
            ->all();
        $users = User::find()
            ->where(['in', 'email', ArrayHelper::getColumn($notIn, 'email')])
//            ->where(['>=', 'id', '171'])
//            ->andWhere(['company_id' => 20])
//            ->andWhere(['<=', 'id', '784'])
//            ->andWhere(['not in', 'email', ArrayHelper::getColumn($notIn, 'email')])
            ->all();
        Yii::info(ArrayHelper::getColumn($users, 'id'));
        if ($users) {
            foreach ($users as $user) {
                $pass = Yii::$app->security->generateRandomString(16);
                $user->password_difficult = 3;
                $user->setPassword($pass);
                $user->generateAuthKey();
                $user->validate();
                if ($user->save()) {
                    print_r(['email' => $user->email, 'pass' => $pass]);
                }
                UserEmailHelper::createdUser($user, $pass);

            }
        }
    }
}
