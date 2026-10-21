<?php

use yii\db\Migration;

/**
 * Class m200319_101005_add_index
 */
class m200319_101005_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('uuid', 'unit', 'uuid');
        $this->createIndex(
            'uuid-unit_uuid-id-delete-is_buffer',
            'product',
            ['uuid', 'unit_uuid', 'id', 'delete', 'is_buffer']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('uuid', 'unit');
        $this->dropIndex('uuid-unit_uuid-id-delete-is_buffer', 'product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200319_101005_add_index cannot be reverted.\n";

        return false;
    }
    */
}
