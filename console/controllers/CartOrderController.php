<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 23.12.2019
 * Time: 14:48
 */

namespace console\controllers;

use common\models\Cart;
use common\models\CartProduct;
use common\models\Order;
use common\models\OrderProduct;
use yii\console\Controller;

/**
 * Class CartOrderController
 * @package console\controllers
 */
class CartOrderController extends Controller
{
    public function actionIndex()
    {
        $orders = Order::find()
            ->all();
        if ($orders) {
            foreach ($orders as $order) {
                $model = new Cart([
                    'company_id' => $order->company_id,
                    'order_id' => $order->id,
                    'user_id' => 0
                ]);
                $model->save();
            }
        }
        $carts = Cart::find()
            ->all();
        if ($carts) {
            foreach ($carts as $cart) {
                $products = OrderProduct::find()
                    ->where(['order_id' => $cart->order_id])
                    ->all();
                if ($products) {
                    foreach ($products as $product) {
                        $model = new CartProduct([
                            'cart_id' => $cart->id,
                            'status' => CartProduct::STATUS_AT_ORDER,
                            'price' => $product->price,
                            'total_sum' => $product->total_sum,
                            'product_id' => $product->id,
                            'product_uuid' => $product->product_uuid,
                            'quantity' => $product->quantity,
                            'new_quantity' => 0
                        ]);
                        $model->save();
                    }
                }
            }
        }
    }
}
