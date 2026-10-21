<?php

use yii\db\Migration;

/**
 * Class m240213_104326_order_table_updarte
 */
class m240213_104326_order_table_updarte extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('order', 'is_budget_reserve', $this->integer()->defaultValue(0));
	    $this->addColumn('order', 'budget_reserve_sum', $this->decimal(10,2)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240213_104326_order_table_updarte cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240213_104326_order_table_updarte cannot be reverted.\n";

        return false;
    }
    */
}
