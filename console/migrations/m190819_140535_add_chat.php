<?php

use yii\db\Migration;

/**
 * Class m190819_140535_add_chat
 */
class m190819_140535_add_chat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('chat_guest_user', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'email' => $this->string(),
            'phone' => $this->string(),
            'ip_address' => $this->string(),
            'created_at' => $this->integer()
        ]);
        $this->execute('ALTER TABLE chat_guest_user AUTO_INCREMENT = 1234567;');

        $this->createTable('chat', [
            'id' => $this->primaryKey(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('chat_user', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer(),
            'user_id' => $this->integer(),
            'is_guest' => $this->smallInteger()
        ]);

        $this->createTable('chat_message', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer(),
            'user_id' => $this->integer(),
            'from_guest' => $this->integer(),
            'message' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('chat_message_read', [
            'id' => $this->primaryKey(),
            'message_id' => $this->integer(),
            'chat_id' => $this->integer(),
            'user_id' => $this->integer(),
            'is_read' => $this->smallInteger()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('chat');
        $this->dropTable('chat_user');
        $this->dropTable('chat_guest_user');
        $this->dropTable('chat_message');
        $this->dropTable('chat_message_read');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190819_140535_add_chat cannot be reverted.\n";

        return false;
    }
    */
}
