<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 12:08
 */
/** @var $model \common\models\Contract */

/** @var $companyEntity \common\models\CompanyEntity */

use yii\helpers\ArrayHelper; ?>
<div>
    Був створений договір <?= $model->name ?> під юридичну особу
    "<?= ArrayHelper::getValue($companyEntity, 'short_name') ?>"
</div>
