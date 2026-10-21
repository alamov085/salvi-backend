<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 02.01.2020
 * Time: 12:43
 */

namespace console\controllers;

use api\helpers\UserEmailHelper;
use common\models\Config;
use common\models\Order;
use common\models\User;
use yii\console\Controller;

/**
 * Class EasterEggsController
 * @package console\controllers
 */
class EasterEggsController extends Controller
{
    public function actionIndex()
    {
        $existSum = Config::getValue(Config::ADMIN_TOTAL_ORDERS);
        $total = Order::find()->where(['status' => Order::STATUS_RECEIVED])->sum('total_sum');
        if ($existSum > $total) {
            return false;
        }
        $user = User::findOne(['id' => 1]);
        if ($total > 100000000) {
            if ($total < 200000000) {
                Config::setValue(Config::ADMIN_TOTAL_ORDERS, 200000000);
                UserEmailHelper::defaultMessage([
                    'view' => 'easterEggs',
                    'user' => $user,
                    'sum' => $existSum
                ]);
            } else {
                Config::setValue(Config::ADMIN_TOTAL_ORDERS, $existSum + 100000000);
                UserEmailHelper::defaultMessage([
                    'view' => 'easterEggs',
                    'user' => $user,
                    'sum' => $existSum
                ]);
            }
        } elseif ($total > 50000000) {
            Config::setValue(Config::ADMIN_TOTAL_ORDERS, 100000000);
            UserEmailHelper::defaultMessage([
                'view' => 'easterEggs',
                'user' => $user,
                'sum' => $existSum
            ]);
        } elseif ($total > 10000000) {
            Config::setValue(Config::ADMIN_TOTAL_ORDERS, 50000000);
            UserEmailHelper::defaultMessage([
                'view' => 'easterEggs',
                'user' => $user,
                'sum' => $existSum
            ]);
        } elseif ($total > 5000000) {
            Config::setValue(Config::ADMIN_TOTAL_ORDERS, 10000000);
            UserEmailHelper::defaultMessage([
                'view' => 'easterEggs',
                'user' => $user,
                'sum' => $existSum
            ]);
        } elseif ($total > 1000000) {
            Config::setValue(Config::ADMIN_TOTAL_ORDERS, 5000000);
            UserEmailHelper::defaultMessage([
                'view' => 'easterEggs',
                'user' => $user,
                'sum' => $existSum
            ]);
        } else {
            return false;
        }
    }
}
