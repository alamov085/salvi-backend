<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.09.2019
 * Time: 16:29
 */
?>
<div>
    Замовлення №<?= $model->id ?>, створене Вами, було відхилено на стадії погодження
    менеджером <?= $cUser->first_name ?> <?= $cUser->last_name ?>.
    <br><a href="https://salvi.ua/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>
</div>
