<?php

use yii\db\Migration;

/**
 * Class m191226_101149_add_to_cart_product
 */
class m191226_101149_add_to_cart_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cart_product', 'last_update', $this->boolean()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cart_product', 'last_update');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191226_101149_add_to_cart_product cannot be reverted.\n";

        return false;
    }
    */
}
