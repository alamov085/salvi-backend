<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 14.02.2020
 * Time: 17:05
 */

namespace console\controllers;

use common\models\Product;
use yii\console\Controller;

/**
 * Class TrimNameController
 * @package console\controllers
 */
class TrimNameController extends Controller
{
    public function actionIndex()
    {
        $products = Product::find()->all();
        if ($products) {
            foreach ($products as $product) {
                $product->name = trim($product->name);
                $product->save();
            }
        }
    }
}
