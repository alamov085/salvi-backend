<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 15.07.2020
 * Time: 13:39
 */

namespace api\job;

use api\helpers\NumberHelper;
use common\models\Cart;
use common\models\CartProduct;
use common\models\Order;
use common\models\OrderProduct;
use common\models\Product;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class DeleteProductJob
 * @package api\job
 */
class DeleteProductJob extends BaseObject implements JobInterface
{
    public $id;
    public $uuid;
    public $is_delete;

    /**
     * @inheritDoc
     */
    public function execute($queue)
    {
        if ($this->is_delete === Product::STATUS_HIDDEN) {
            return true;
        }
        CartProduct::updateAll(['is_delete' => $this->is_delete], ['product_id' => $this->id]);
        $cartIds = CartProduct::find()->select('cart_id')
            ->where(['product_id' => $this->id])
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
        $orders = Order::find()
            ->select(['id'])
            ->where([
                'status' => [
                    Order::STATUS_OPEN,
                    Order::STATUS_ON_APPROVE,
                    Order::STATUS_MODERATION,
                    Order::STATUS_MOD_APPROVE
                ]
            ])
            ->asArray()
            ->all();
        if ($orders) {
            OrderProduct::updateAll(
                ['is_delete' => $this->is_delete],
                [
                    'and',
                    ['product_id' => $this->id],
                    ['order_id' => ArrayHelper::getColumn($orders, 'id')]
                ]
            );
            $orderIds = OrderProduct::find()
                ->select(['order_id'])
                ->where(['product_id' => $this->id])
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
}
