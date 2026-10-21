<?php

use yii\db\Migration;

/**
 * Class m190827_064027_table_notification
 */
class m190827_064027_table_notification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('notification', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'is_read' => $this->integer()->defaultValue(0),
            'message' => $this->string(),
            'status' => $this->integer()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('notification');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190827_064027_table_notification cannot be reverted.\n";

        return false;
    }
    */
}
