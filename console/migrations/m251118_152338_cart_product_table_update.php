<?php

use yii\db\Migration;

/**
 * Class m251118_152338_cart_product_table_update
 */
class m251118_152338_cart_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cart_product', 'discount_price', $this->decimal(10, 2)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251118_152338_cart_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251118_152338_cart_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
