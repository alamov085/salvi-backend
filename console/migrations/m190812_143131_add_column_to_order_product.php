<?php

use yii\db\Migration;

/**
 * Class m190812_143131_add_column_to_order_product
 */
class m190812_143131_add_column_to_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_product', 'view_only', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'view_only');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190812_143131_add_column_to_order_product cannot be reverted.\n";

        return false;
    }
    */
}
