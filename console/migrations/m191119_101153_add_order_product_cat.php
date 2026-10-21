<?php

use yii\db\Migration;

/**
 * Class m191119_101153_add_order_product_cat
 */
class m191119_101153_add_order_product_cat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_product', 'parent_category_uuid', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_product', 'parent_category_uuid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191119_101153_add_order_product_cat cannot be reverted.\n";

        return false;
    }
    */
}
