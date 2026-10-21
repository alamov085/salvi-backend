<?php

use yii\db\Migration;

/**
 * Class m230606_145125_order_product_history
 */
class m230606_145125_order_product_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->createTable('order_product_change_history', [
		    'id' => $this->primaryKey(),
		    'order_id' => $this->integer(),
		    'product_id' => $this->integer(),
		    'product_uuid' => $this->string(),
		    'quantity' => $this->integer(),
		    'price' => $this->decimal(16, 2),
		    'total_sum' => $this->decimal(16, 2),
		    'created_at' => $this->integer(),
		    'catalog' => $this->integer(),
		    'type_event' => $this->string(),
		    'name' => $this->string(),
		    'is_note' => $this->integer()->defaultValue(0),
	    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230606_145125_order_product_history cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230606_145125_order_product_history cannot be reverted.\n";

        return false;
    }
    */
}
