<?php

use yii\db\Migration;

/**
 * Class m191223_121030_add_cart_model
 */
class m191223_121030_add_cart_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cart', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull()->defaultValue(0),
            'company_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('cart_product', [
            'id' => $this->primaryKey(),
            'cart_id' => $this->integer(),
            'product_id' => $this->integer(),
            'product_uuid' => $this->string(),
            'quantity' => $this->integer(),
            'new_quantity' => $this->integer(),
            'price' => $this->decimal(16, 2),
            'total_sum' => $this->decimal(16, 2),
            'status' => $this->integer()->notNull()->defaultValue(0)
        ]);

        $this->createTable('cart_product_history', [
            'id' => $this->primaryKey(),
            'cart_id' => $this->integer(),
            'user_id' => $this->integer(),
            'username' => $this->string(),
            'message' => $this->string(),
            'created_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cart');
        $this->dropTable('cart_product');
        $this->dropTable('cart_product_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191223_121030_add_cart_model cannot be reverted.\n";

        return false;
    }
    */
}
