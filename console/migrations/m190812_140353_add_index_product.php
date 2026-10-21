<?php

use yii\db\Migration;

/**
 * Class m190812_140353_add_index_product
 */
class m190812_140353_add_index_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('product-uuid', 'product', 'uuid', true);
        $this->createIndex('category-uuid', 'product_category', 'category_uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('product-uuid', 'product');
        $this->dropIndex('category-uuid', 'product_category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190812_140353_add_index_product cannot be reverted.\n";

        return false;
    }
    */
}
