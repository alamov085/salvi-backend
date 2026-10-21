<?php

use yii\db\Migration;

/**
 * Class m191107_134515_add_table_unisender
 */
class m191107_134515_add_table_unisender extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('unisender_sended', [
            'id' => $this->primaryKey(),
            'email' => $this->string(),
            'subject' => $this->text(),
            'message' => $this->text(),
            'id_message' => $this->string(64),
            'code' => $this->string(),
            'errors' => $this->string(),
            'created_at' => $this->integer(11),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('unisender_sended');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191107_134515_add_table_unisender cannot be reverted.\n";

        return false;
    }
    */
}
