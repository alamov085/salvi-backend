<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 18:41
 */

/** @var $user \common\models\User */

/** @var $reason string */

use common\models\Company;
use yii\helpers\ArrayHelper;

$company = Company::find()->where(['id' => $user->company_id])->one();

?>
<div>
    Вашу заявку на реєстрацію підприємства "<?= ArrayHelper::getValue($company->short_name, '') ?>"
    відхилили по причині: "<?= $reason ?>"
</div>
