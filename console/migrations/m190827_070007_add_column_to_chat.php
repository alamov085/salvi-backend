<?php

use yii\db\Migration;

/**
 * Class m190827_070007_add_column_to_chat
 */
class m190827_070007_add_column_to_chat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('chat', 'user_id', $this->integer());
        $this->addColumn('chat', 'is_guest', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('chat', 'user_id');
        $this->addColumn('chat', 'is_guest');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190827_070007_add_column_to_chat cannot be reverted.\n";

        return false;
    }
    */
}
