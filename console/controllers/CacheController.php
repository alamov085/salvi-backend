<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 14:48
 */

namespace console\controllers;

use Yii;
use yii\console\Controller;


class CacheController extends Controller
{
    public function actionClear()
    {
        Yii::app()->cache->flush();

    }
}
