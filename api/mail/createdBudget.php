<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.09.2019
 * Time: 10:57
 */

use yii\helpers\ArrayHelper;

/** @var $model \common\models\Budget */
/** @var $locations array */
/** @var $costs array */
if ($locations) {
    if (count($locations) > 1) {
        $text = 'локації: ';
        foreach ($locations as $k => $location) {
            if ($k) {
                $text .= ', ' . $location->name;
            } else {
                $text .= $location->name;
            }
        }
    } else {
        $text = 'локацію ' . ArrayHelper::getValue($locations, '0.name');
    }
    if ($model->all_cost) {
        $costText = ' і всі кост-центри';
    } else {
        if ($costs) {
            if (count($costs) > 1) {
                $costText = 'кост-центри: ';
                foreach ($costs as $k => $cost) {
                    if ($k) {
                        $costText .= ', ' . $cost->name;
                    } else {
                        $costText .= $cost->name;
                    }
                }
            } else {
                $costText = 'кост-центр ' . ArrayHelper::getValue($costs, '0.name');
            }
        } else {
            $costText = '';
        }
    }
}
?>
<div>
    На <?= $text ?> <?= $costText ?> було створено бюджет "<?= $model->name ?>" для Вашого підприємства.
</div>
