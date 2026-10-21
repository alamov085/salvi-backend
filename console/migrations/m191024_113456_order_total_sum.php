<?php

use yii\db\Migration;

/**
 * Class m191024_113456_order_total_sum
 */
class m191024_113456_order_total_sum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('order', 'total_sum', 'decimal(10,2)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('order', 'total_sum', 'int NOT NULL DEFAULT 0');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191024_113456_order_total_sum cannot be reverted.\n";

        return false;
    }
    */
}
