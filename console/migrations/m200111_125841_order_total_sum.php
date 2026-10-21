<?php

use yii\db\Migration;

/**
 * Class m200111_125841_order_total_sum
 */
class m200111_125841_order_total_sum extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('order', 'total_sum', $this->decimal(10, 2)->notNull()->defaultValue(0));
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
        echo "m200111_125841_order_total_sum cannot be reverted.\n";

        return false;
    }
    */
}
