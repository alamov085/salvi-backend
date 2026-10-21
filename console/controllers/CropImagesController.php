<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.07.2020
 * Time: 14:05
 */

namespace console\controllers;

use common\models\ProductPhoto;
use Yii;
use yii\console\Controller;

/**
 * Class CropImagesController
 * @package console\controllers
 */
class CropImagesController extends Controller
{
    public function actionIndex()
    {
        $path = Yii::getAlias('@api') . '/web/product/';

//        $images = \yii\helpers\FileHelper::findFiles($path);

        $images = ProductPhoto::find()->all();

        if ($images) {
            foreach ($images as $image) {
                $img = $path . $image->name;
//                $img = $image;
                ProductPhoto::cropPhoto($img, 260, 400);
            }
        }
    }
}
