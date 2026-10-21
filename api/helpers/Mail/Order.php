<?php

namespace api\helpers\Mail;

use Yii;

class Order {

    public static function repeatToStartOrder($order) {
//        \common\models\Order::

        $subject = '=?utf-8?B?'. base64_encode("Ваше замовлення №{$order->id} не було погоджено").'?=';;
	    
	    $view = "Ваше замовлення №{$order->id} не було погоджено.<br>";
        $view .= "ВСкопіюйте замовлення, відредагуйте список товарів та передайте замовлення на узгодження повторно.<br>";
        $view .= "<a href='".Yii::$app->params['frontedDomain']."/orders/?order={$order->id}'>Перейти до замовлення</a><br>";
        $view .= '<a href="'.Yii::$app->params['frontedDomain'] .'/unsubscribe-mails" target="_blank">Відписатися від розсилки листів</a>';

        return self::sendEmail($view, $order->getCreatedBy()->one()->email, $subject);
    }

    private function sendEmail(string $body, string $receiver, string $subject) {
    	
        $mail = Yii::$app
            ->mailer
            ->compose()
            ->setFrom(Yii::$app->params['emailAccount'])
            ->setTo($receiver)
            ->setSubject($subject)
            ->setHtmlBody($body);

        $log = $mail->toString();

        return $mail->send();
    }
}