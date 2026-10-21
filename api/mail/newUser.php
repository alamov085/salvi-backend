<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */
/* @var $password string */
//$password = str_replace('$', '\$', $password);
?>
<div class="verify-email">
    <p>Вам створений акаунт.</p>
    <p>Ваш логін: <?= Html::encode($user->email) ?></p>
    <p>Ваш пароль: <?= $password ?></p>
    <p>Для авторизації перейдіть за посиланням: <?= Html::a(
            Yii::$app->params['frontedDomain'] . '/login',
            Yii::$app->params['frontedDomain'] . '/login'
        ) ?></p>
</div>
