<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.07.2019
 * Time: 11:52
 */

namespace console\controllers;

use common\models\CompanyEntity;
use yii\console\Controller;

/**
 * Class CompanyEntityController
 * @package console\controllers
 */
class CompanyEntityController extends Controller
{
    public function actionIndex()
    {
        $data = CompanyEntity::find()
            ->where(['status_check' => 0])
            ->andWhere(['status' => CompanyEntity::STATUS_ACTIVE])
            ->all();
        $time = time();
        if ($data != false) {
            foreach ($data as $company) {
                if ($time > $company->from_date) {
                    $company->status = CompanyEntity::STATUS_DISMISSED;
                    $company->save();
                }
            }
        }
    }
}
