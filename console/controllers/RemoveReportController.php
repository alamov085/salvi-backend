<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 09.07.2020
 * Time: 14:39
 */

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\FileHelper;

/**
 * Class RemoveReportController
 * @package console\controllers
 */
class RemoveReportController extends Controller
{
    public function actionIndex()
    {
        $time = time();
        $files = FileHelper::findFiles(Yii::getAlias('@api/web') . '/report', ['only' => ['*.xlsx']]);
        if ($files) {
            foreach ($files as $file) {
                if (file_exists($file) && ($time - 60 * 10) > filemtime($file)) {
                    unlink($file);
                }
            }
        }
    }
}
