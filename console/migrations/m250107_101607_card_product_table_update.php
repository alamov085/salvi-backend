<?php

use yii\db\Migration;

/**
 * Class m250107_101607_card_product_table_update
 */
class m250107_101607_card_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('cart_product', 'is_strict_multiplicity', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250107_101607_card_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250107_101607_card_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
