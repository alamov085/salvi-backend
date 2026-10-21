<?php

use yii\db\Migration;

/**
 * Class m191011_122138_defaul_val_order_sum
 */
class m191011_122138_defaul_val_order_sum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('order', 'total_sum', 'integer NOT NULL DEFAULT 0');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('order', 'total_sum', 'integer NOT NULL');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191011_122138_defaul_val_order_sum cannot be reverted.\n";

        return false;
    }
    */
}
