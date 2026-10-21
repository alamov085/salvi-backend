<?php

use yii\db\Migration;

/**
 * Class m190813_074921_add_order_product
 */
class m190813_074921_add_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_product', 'custom', $this->integer()->defaultValue(0));
        $this->addColumn('order_product', 'name', $this->string());
        $this->addColumn('order_product', 'comment', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'custom');
        $this->dropColumn('order_product', 'name');
        $this->dropColumn('order_product', 'comment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190813_074921_add_order_product cannot be reverted.\n";

        return false;
    }
    */
}
