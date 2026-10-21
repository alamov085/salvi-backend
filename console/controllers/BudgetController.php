<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 20.07.2019
 * Time: 15:11
 */

namespace console\controllers;

use common\models\Budget;
use yii\console\Controller;

/**
 * Class BudgetController
 * @package console\controllers
 */
class BudgetController extends Controller
{
    /**
     * Закрытие бюджетов по дате
     */
    public function actionIndex()
    {
        $time = strtotime('today');
        Budget::updateAll(
            ['status' => Budget::STATUS_ACTIVE],
            ['and',
                ['status' => Budget::STATUS_INACTIVE],
                [
                    'or',
                    ['<', 'date_start', $time],
                    ['>', 'date_end', $time],
                ]
            ]
        );
        Budget::updateAll(
            ['status' => Budget::STATUS_INACTIVE],
            ['and',
                ['status' => Budget::STATUS_ACTIVE],
                [
                    'or',
                    ['>', 'date_start', $time],
                    ['<', 'date_end', $time - 86400],
                ]
            ]
        );
    }
}
