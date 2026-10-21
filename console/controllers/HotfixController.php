<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 19.11.2019
 * Time: 12:27
 */

namespace console\controllers;

use common\models\Category;
use common\models\Order;
use common\models\OrderProduct;
use common\models\ProductCategory;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class HotfixController
 * @package console\controllers
 */
class HotfixController extends Controller
{
    public function actionParentCatToOrder()
    {
        $orderProducts = OrderProduct::find()->all();
        if ($orderProducts) {
            $categories = Category::find()
                ->where(['is', 'parent_id', null])
                ->asArray()
                ->all();
            $productsCat = ProductCategory::find()
                ->where(['in', 'category_uuid', ArrayHelper::getColumn($categories, 'uuid')])
                ->asArray()
                ->all();
            $products = ArrayHelper::index($productsCat, 'product_uuid');
            foreach ($orderProducts as $orderProduct) {
                $orderProduct->parent_category_uuid = ArrayHelper::getValue(
                    $products,
                    $orderProduct->product_uuid . '.category_uuid',
                    ''
                );
                $orderProduct->save();
            }
        }
    }

    public function actionFixStat()
    {
        Order::deleteAll(['company_id'=>3]);
        $orders = Order::find()
            ->select(['id'])
            ->asArray()
            ->all();

        OrderProduct::deleteAll(['not in', 'order_id', ArrayHelper::getColumn($orders, 'id')]);
    }
}
