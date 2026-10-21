<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 18:23
 */
/** @var $password string */
/* @var $user common\models\User */


use common\models\User;
use yii\helpers\Html;

$password = str_replace('$', '\$', $password);

?>
<div>
    <p>Вам створений акаунт.</p>
    <p>Ваш логін: <?= Html::encode($user->email) ?></p>
    <p>Ваш пароль: <?= $password ?></p>
    <p>Для авторизації перейдіть за посиланням: <?= Html::a(
            Yii::$app->params['frontedDomain'] . '/login',
            Yii::$app->params['frontedDomain'] . '/login'
        ) ?></p>
</div>
