<?php

use yii\db\Migration;

/**
 * Class m231227_095625_product_table_update
 */
class m231227_095625_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('product', 'leftovers_salvi', $this->integer()->defaultValue(0));
	    $this->addColumn('product', 'leftovers_supplier', $this->integer()->defaultValue(0));
	    $this->addColumn('product', 'supplier_delivery_postpone', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231227_095625_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231227_095625_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
