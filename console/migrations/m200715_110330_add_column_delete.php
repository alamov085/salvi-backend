<?php

use yii\db\Migration;

/**
 * Class m200715_110330_add_column_delete
 */
class m200715_110330_add_column_delete extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cart_product', 'is_delete', $this->boolean()->defaultValue(0));
        $this->addColumn('order_product', 'is_delete', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cart_product', 'is_delete');
        $this->dropColumn('order_product', 'is_delete');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200715_110330_add_column_delete cannot be reverted.\n";

        return false;
    }
    */
}
