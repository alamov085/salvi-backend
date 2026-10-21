<?php

use yii\db\Migration;

/**
 * Class m251118_104233_update_product_table
 */
class m251118_104233_update_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'discount_price', $this->decimal(10, 2)->defaultValue(0));
        $this->addColumn('product', 'discount_expire_date', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251118_104233_update_product_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251118_104233_update_product_table cannot be reverted.\n";

        return false;
    }
    */
}
