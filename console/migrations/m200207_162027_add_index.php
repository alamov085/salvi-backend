<?php

use yii\db\Migration;

/**
 * Class m200207_162027_add_index
 */
class m200207_162027_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('parent_category_uuid_order_id', 'order_product', ['parent_category_uuid', 'order_id']);
        $this->createIndex('auth_key_status', 'user', ['auth_key', 'status']);
        $this->createIndex('is_read_user_id', 'chat_message_read', ['is_read', 'user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('parent_category_uuid_order_id', 'order_product');
        $this->dropIndex('auth_key_status', 'user');
        $this->dropIndex('is_read_user_id', 'chat_message_read');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200207_162027_add_index cannot be reverted.\n";

        return false;
    }
    */
}
