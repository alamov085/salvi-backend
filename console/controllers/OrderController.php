<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 26.11.2019
 * Time: 16:56
 */

namespace console\controllers;


use api\helpers\OrderHelper;
use common\models\Order;
use common\models\OrderProduct;
use yii\console\Controller;

class OrderController extends Controller
{
    /** @var  int */
    public $id;

    /**
     * @param string $actionID
     * @return array
     */
    public function options($actionID)
    {
        return ['id'];
    }

    public function actionCopy()
    {
        $order = Order::findOne(['id' => $this->id]);
        if ($order) {
            $newOrder = $order->copyOrder();
            if ($newOrder) {
                $products = OrderProduct::find()->where(['order_id' => $this->id])->all();
                if ($products) {
                    foreach ($products as $product) {
                        $newProduct = new OrderProduct();
                        $newProduct->setAttributes($product->attributes);
                        $newProduct->order_id = $newOrder->id;
                        $newProduct->approved = 0;
                        $newProduct->save();
                    }
                }
                $newOrder->status = Order::STATUS_OPEN;
                $newOrder->save();
                return $newOrder->id;
            }
        }
    }
}
