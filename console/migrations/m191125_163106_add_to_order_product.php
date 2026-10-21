<?php

use yii\db\Migration;

/**
 * Class m191125_163106_add_to_order_product
 */
class m191125_163106_add_to_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_product', 'catalog', $this->integer()->notNull()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'catalog');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191125_163106_add_to_order_product cannot be reverted.\n";

        return false;
    }
    */
}
