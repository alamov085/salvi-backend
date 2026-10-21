<?php

use yii\db\Migration;

/**
 * Class m191030_093405_add_colummn_to_order_product
 */
class m191030_093405_add_colummn_to_order_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_product', 'approved', $this->integer()->defaultValue(0));
        $this->addColumn('order_product', 'approved_quantity', $this->integer()->defaultValue(0));
        $this->addColumn('order_product', 'spec_quantity', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'approved');
        $this->dropColumn('order_product', 'approved_quantity');
        $this->dropColumn('order_product', 'spec_quantity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191030_093405_add_colummn_to_order_product cannot be reverted.\n";

        return false;
    }
    */
}
