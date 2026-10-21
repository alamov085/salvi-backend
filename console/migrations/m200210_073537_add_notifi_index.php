<?php

use yii\db\Migration;

/**
 * Class m200210_073537_add_notifi_index
 */
class m200210_073537_add_notifi_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('user_id_is_read', 'notification', ['user_id', 'is_read']);
        $this->createIndex('id_status', 'company', ['id', 'status']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('user_id_is_read', 'notification');
        $this->dropIndex('id_status', 'company');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200210_073537_add_notifi_index cannot be reverted.\n";

        return false;
    }
    */
}
