<?php

use yii\db\Migration;

/**
 * Class m191112_172338_add_notification
 */
class m191112_172338_add_notification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('notification_id', 'notification', ['user_id', 'status']);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('notification_id', 'notification');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191112_172338_add_notification cannot be reverted.\n";

        return false;
    }
    */
}
