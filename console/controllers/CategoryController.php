<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 25.05.2020
 * Time: 12:21
 */

namespace console\controllers;

use common\models\Category;
use common\models\Product;
use common\models\ProductCategory;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class CategoryController
 * @package console\controllers
 */
class CategoryController extends Controller
{
    public function actionIndex()
    {
        $productsDeleted = Product::find()
            ->select(['uuid'])
            ->where(['delete' => 1])
            ->andWhere(['is_buffer' => 0])
            ->asArray()
            ->all();
        $categoriesDeleted = ProductCategory::find()
            ->select(['category_uuid'])
            ->where(['in', 'product_uuid', ArrayHelper::getColumn($productsDeleted, 'uuid')])
            ->groupBy('category_uuid')
            ->asArray()
            ->all();

        $productsActive = Product::find()
            ->select(['uuid'])
            ->where(['delete' => 0])
            ->andWhere(['is_buffer' => 0])
            ->asArray()
            ->all();
        $categoriesActive = ProductCategory::find()
            ->select(['category_uuid'])
            ->where(['in', 'product_uuid', ArrayHelper::getColumn($productsActive, 'uuid')])
            ->groupBy('category_uuid')
            ->asArray()
            ->all();
//        $result = array_diff(
//            ArrayHelper::getColumn($categoriesDeleted, 'category_uuid'),
//            ArrayHelper::getColumn($categoriesActive, 'category_uuid')
//        );
        Category::updateAll(['delete' => 1]);
        Category::updateAll(['delete' => 0], ['in', 'uuid', ArrayHelper::getColumn($categoriesActive, 'category_uuid')]);
    }
}
