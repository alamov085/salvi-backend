<?php

use common\models\Order;
use common\models\OrderProduct;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

/**
 * Class m200211_124011_add_to_order_product
 */
class m200211_124011_add_to_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'order_product',
            'on_approve_quantity',
            $this->integer()->defaultValue(0)->comment('Количество товара при переводе в статус на согласовании')
        );
        $orders = Order::find()->where(['status' => Order::STATUS_ON_APPROVE])->all();
        if ($orders) {
            $products = OrderProduct::find()
                ->where(['in', 'order_id', ArrayHelper::getColumn($orders, 'id')])
                ->all();
            if ($products) {
                foreach ($products as $product) {
                    $product->on_approve_quantity = $product->quantity;
                    $product->save();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'on_approve_quantity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200211_124011_add_to_order_product cannot be reverted.\n";

        return false;
    }
    */
}
