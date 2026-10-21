<?php

use yii\db\Migration;

/**
 * Class m250304_085747_cart_product_table_update
 */
class m250304_085747_cart_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('cart_product', 'is_temperature_conditions', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250304_085747_cart_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250304_085747_cart_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
