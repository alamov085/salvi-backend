<?php

use yii\db\Migration;

/**
 * Class m191008_123646_create_index
 */
class m191008_123646_create_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('product-uuid', 'product_category', 'product_uuid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('product-uuid', 'product_category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191008_123646_create_index cannot be reverted.\n";

        return false;
    }
    */
}
