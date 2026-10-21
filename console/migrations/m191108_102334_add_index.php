<?php

use yii\db\Migration;

/**
 * Class m191108_102334_add_index
 */
class m191108_102334_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('product_id', 'product_category', ['product_id']);
        $this->createIndex('uuid', 'category', ['uuid']);
        $this->createIndex('product_uuid', 'product_photo', ['product_uuid']);
        $this->createIndex('uuid_and_id', 'specification_product', ['product_uuid', 'specification_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('product_id', 'product_category');
        $this->dropIndex('uuid', 'category');
        $this->dropIndex('product_uuid', 'product_photo');
        $this->dropIndex('uuid_and_id', 'specification_product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191108_102334_add_index cannot be reverted.\n";

        return false;
    }
    */
}
