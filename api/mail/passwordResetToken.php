<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->params['frontedDomain'] . '/reset-password?token=' . $user->password_reset_token;
?>
<div class="password-reset">
    <p>Добрий день, <?= Html::encode($user->username) ?>,</p>

    <p>Щоб скинути пароль, перейдіть за посиланням нижче:</p>

    <p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>
</div>
