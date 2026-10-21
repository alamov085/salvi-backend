<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 04.12.2019
 * Time: 14:59
 */

namespace console\controllers;

use common\models\Budget;
use common\models\Company;
use common\models\CompanyEntity;
use common\models\Locations;
use common\models\Order;
use common\models\Specification;
use common\models\User;
use yii\console\Controller;

/**
 * Class DeleteController
 * @package console\controllers
 */
class DeleteController extends Controller
{
    public function actionIndex()
    {
        $ids = [238];
        Company::deleteAll(['in', 'id', $ids]);
        Budget::deleteAll(['in', 'company_id', $ids]);
        Order::deleteAll(['in', 'company_id', $ids]);
        Locations::deleteAll(['in', 'company_id', $ids]);
        CompanyEntity::deleteAll(['in', 'company_id', $ids]);
        Specification::deleteAll(['in', 'company_id', $ids]);
        User::deleteAll([
            'and',
            ['in', 'company_id', $ids],
            ['!=', 'manager', 1]
        ]);
    }
}
