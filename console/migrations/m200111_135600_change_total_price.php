<?php

use yii\db\Migration;

/**
 * Class m200111_135600_change_total_price
 */
class m200111_135600_change_total_price extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('order_product', 'total_sum', $this->decimal(16, 3)->notNull()->defaultValue(0));
        $this->alterColumn('cart_product', 'total_sum', $this->decimal(16, 3)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('order_product', 'total_sum', $this->decimal(16, 2)->notNull()->defaultValue(0));
        $this->alterColumn('cart_product', 'total_sum', $this->decimal(16, 2)->notNull()->defaultValue(0));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200111_135600_change_total_price cannot be reverted.\n";

        return false;
    }
    */
}
