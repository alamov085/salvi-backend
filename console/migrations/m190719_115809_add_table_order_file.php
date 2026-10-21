<?php

use yii\db\Migration;

/**
 * Class m190719_115809_add_table_order_file
 */
class m190719_115809_add_table_order_file extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_file', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'old_name' => $this->string(),
            'new_name' => $this->string(),
            'ext' => $this->string(),
            'size' => $this->integer(),
            'user_id' => $this->integer(),
            'created_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order_file');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190719_115809_add_table_order_file cannot be reverted.\n";

        return false;
    }
    */
}
