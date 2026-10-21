<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 16:42
 */
/** @var $cUser \common\models\User */
?>
<div>
    Користувач <?= $cUser->first_name ?> <?= $cUser->last_name ?> залишив коментар до
    замовлення №<?= $model->id ?>
    <br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>
</div>
