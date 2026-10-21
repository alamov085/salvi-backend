<?php

use yii\db\Migration;

/**
 * Class m240425_163600_cart_product_update
 */
class m240425_163600_cart_product_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('cart_product', 'is_specification', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240425_163600_cart_product_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240425_163600_cart_product_update cannot be reverted.\n";

        return false;
    }
    */
}
