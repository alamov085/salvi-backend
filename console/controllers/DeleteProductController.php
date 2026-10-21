<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.07.2020
 * Time: 15:15
 */

namespace console\controllers;

use api\helpers\NumberHelper;
use common\models\Cart;
use common\models\CartProduct;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Class DeleteProductController
 * @package console\controllers
 */
class DeleteProductController extends Controller
{
    public function actionIndex()
    {
        $productIds = Product::find()
            ->select(['id'])
            ->where(['delete' => 1])
            ->asArray()
            ->all();
        $this->cart($productIds);
        $this->order($productIds);
    }

    private function order($productIds)
    {
        $orders = Order::find()
            ->select(['id'])
            ->where([
                'status' => [
                    Order::STATUS_OPEN,
                    Order::STATUS_ON_APPROVE,
                    Order::STATUS_AT_MANAGER,
                    Order::STATUS_MODERATION,
                    Order::STATUS_MOD_APPROVE
                ]
            ])
            ->asArray()
            ->all();
        if ($orders) {
            OrderProduct::updateAll(
                ['is_delete' => 1],
                [
                    'and',
                    ['product_id' => ArrayHelper::getColumn($productIds, 'id')],
                    ['order_id' => ArrayHelper::getColumn($orders, 'id')]
                ]
            );
            $orderIds = OrderProduct::find()
                ->select(['order_id'])
                ->where(['product_id' => ArrayHelper::getColumn($productIds, 'id')])
                ->andWhere(['order_id' => ArrayHelper::getColumn($orders, 'id')])
                ->asArray()
                ->all();
            if ($orderIds) {
                $orders = Order::find()
                    ->where(['id' => ArrayHelper::getColumn($orderIds, 'order_id')])
                    ->all();
                foreach ($orders as $order) {
                    $total = OrderProduct::find()
                        ->where(['order_id' => $order->id])
                        ->andWhere(['view_only' => 0])
                        ->andWhere(['is_delete' => 0])
                        ->sum('total_sum');
                    $order->total_sum = NumberHelper::convert($total);
                    $order->save();
                }

            }
        }
    }

    private function cart($productIds)
    {
        if ($productIds) {
            CartProduct::updateAll(
                ['is_delete' => 1],
                ['product_id' => ArrayHelper::getColumn($productIds, 'id')]
            );
            $cartIds = CartProduct::find()->select('cart_id')
                ->where(['product_id' => ArrayHelper::getColumn($productIds, 'id')])
                ->groupBy('cart_id')
                ->asArray()
                ->all();
            if ($cartIds) {
                $carts = Cart::find()
                    ->where(['id' => ArrayHelper::getColumn($cartIds, 'cart_id')])
                    ->all();
                if ($carts) {
                    foreach ($carts as $cart) {
                        $cart->recalculateTotal();
                    }
                }
            }
        }
    }
}
