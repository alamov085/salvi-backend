<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 12:08
 */
/** @var int $order_id */
$order = \common\models\Order::find()->joinWith(['location'])->where(['order.id' => $order_id])->one();
$link = Yii::$app->params['frontedDomain'] . '/orders/?order=' . $order_id;
?>
<div>
    За локацією "<?= $order->location->name ?>" створено нове замовлення №<?= $order_id ?>, що потребує Вашого
    погодження. Для ознайомлення з замовленням перейдіть за посиланням нижче.
    <p>
        <?= \yii\helpers\Html::a('До замовлення', $link) ?>
    </p>
    <br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>
</div>
