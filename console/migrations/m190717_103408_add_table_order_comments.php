<?php

use yii\db\Migration;

/**
 * Class m190717_103408_add_table_order_comments
 */
class m190717_103408_add_table_order_comments extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_comment', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'user_id' => $this->integer(),
            'text' => $this->text(),
            'created_at' => $this->integer(11)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order_comment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190717_103408_add_table_order_comments cannot be reverted.\n";

        return false;
    }
    */
}
