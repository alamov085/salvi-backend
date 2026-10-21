<?php

use yii\db\Migration;

/**
 * Class m191120_091115_add_index
 */
class m191120_091115_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('company_id', 'order', ['company_id']);
        $this->createIndex('order_id', 'order_product', ['order_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('company_id', 'order');
        $this->dropIndex('order_id', 'order_product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191120_091115_add_index cannot be reverted.\n";

        return false;
    }
    */
}
