<?php

use yii\db\Migration;

/**
 * Class m250228_151456_product_order_table_update
 */
class m250228_151456_product_order_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->alterColumn('order_product_change_history', 'quantity', $this->decimal(16,3));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250228_151456_product_order_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250228_151456_product_order_table_update cannot be reverted.\n";

        return false;
    }
    */
}
