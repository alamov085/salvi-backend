<?php

use yii\db\Migration;

/**
 * Class m231122_191536_product_table_update
 */
class m231122_191536_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('product', 'multiplicity', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m231122_191536_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m231122_191536_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
