<?php

use common\models\Company;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */
/* @var $password string */

$verifyLink = Yii::$app->params['frontedDomain'] . '/verify-email?token=' . $user->verification_token;
$company = Company::find()->where(['id'=>$user->company_id])->one();

$password = str_replace('$', '\$', $password);
?>
<div class="verify-email">
    <p>Добрий день, <?= Html::encode($user->username) ?>,</p>
    <p>Вашу заявку на реєстрацію підприємства "<?= ArrayHelper::getValue($company, 'short_name') ?>" схвалили.</p>
    <p>Ваш пароль: <?=$password ?></p>
    <p>Перейдіть за посиланням нижче, щоб підтвердити свою електронну пошту:</p>
    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>
