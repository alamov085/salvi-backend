<?php

use yii\db\Migration;

/**
 * Class m191031_132052_add_column_to_order_product
 */
class m191031_132052_add_column_to_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_product', 'start_quantity', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'start_quantity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191031_132052_add_column_to_order_product cannot be reverted.\n";

        return false;
    }
    */
}
