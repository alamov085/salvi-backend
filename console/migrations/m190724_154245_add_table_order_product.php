<?php

use yii\db\Migration;

/**
 * Class m190724_154245_add_table_order_product
 */
class m190724_154245_add_table_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_product', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'product_id' => $this->integer(),
            'product_uuid' => $this->string(),
            'quantity' => $this->integer(),
            'price' => $this->decimal(16, 2),
            'total_sum' => $this->decimal(16, 2),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order_product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190724_154245_add_table_order_product cannot be reverted.\n";

        return false;
    }
    */
}
