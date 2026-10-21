<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.09.2019
 * Time: 15:09
 */

namespace console\controllers;

use api\helpers\NotificationHelper;
use common\models\Contract;
use yii\console\Controller;

/**
 * Class ContractController
 * @package console\controllers
 */
class ContractController extends Controller
{
    public function actionIndex()
    {
        echo date('d-m-Y H:i:s') . PHP_EOL;

            $this->sevenDays();
//        $this->threeDays();
//        $this->oneDay();
//        $this->ended();
    }

    private function sevenDays()
    {


        $timeTo = strtotime('+7 days',strtotime(date('d.m.Y H:00:00')));

        $timeFrom = time();

        $contracts = Contract::find()
            ->where(['<', 'date_end', $timeTo])
            ->andWhere(['>', 'date_end', $timeFrom])
            ->andWhere(['=', 'status', 1])
            ->all();

        if ($contracts)
        {
            foreach ($contracts as $contract)
            {
                echo 'Выбрали - ' . $contract->id . PHP_EOL;

                $dayLastSendNoty = (is_null($contract->date_send_noty_end)) ? 0 : $contract->date_send_noty_end;

                $canResendNoty = true;

                if($dayLastSendNoty > 0)
                {
                    if(time() - $dayLastSendNoty < 3600 * 24 * 7 + 3600)
                    {
                        $canResendNoty = false;
                    }
                }

                if(is_null($contract->date_send_noty_end) && $canResendNoty )
                {
                    echo 'Отправляем - ' . $contract->id . PHP_EOL;

                    NotificationHelper::soonEndContractCompany($contract, '7 днів');

                    $contract->date_send_noty_end = time();

                    $contract->save();
                }
            }
        }
    }

    public function actionTest ()
    {
        $contracts = Contract::find()
            ->where(['>', 'date_send_noty_end', 2])
            ->all();

        echo '<pre>';
        print_r($contracts);
        echo '</pre>';
        die();
    }

    private function threeDays()
    {
        $time = time() + 3600 * 24 * 3;
        $contracts = Contract::find()
            ->where(['<', 'date_end', $time])
            ->andWhere(['>', 'date_end', $time - 300])
            ->all();
        if ($contracts) {
            foreach ($contracts as $contract) {
                NotificationHelper::soonEndContract($contract, '3 дні');
            }
        }
    }

    private function oneDay()
    {
        $time = time() + 3600 * 24;
        $contracts = Contract::find()
            ->where(['<', 'date_end', $time])
            ->andWhere(['>', 'date_end', $time - 300])
            ->all();
        if ($contracts) {
            foreach ($contracts as $contract) {
                NotificationHelper::soonEndContract($contract);
            }
        }
    }

    private function ended()
    {
        $time = time();
        $contracts = Contract::find()
            ->where(['<', 'date_end', $time])
            ->andWhere(['>', 'date_end', $time - 300])
            ->all();
        print_r($contracts);
//        if ($contracts) {
//            foreach ($contracts as $contract) {
//                NotificationHelper::endedContract($contract);
//            }
//        }
    }
}
