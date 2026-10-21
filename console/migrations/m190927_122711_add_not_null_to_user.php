<?php

use yii\db\Migration;

/**
 * Class m190927_122711_add_not_null_to_user
 */
class m190927_122711_add_not_null_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'first_name', 'varchar(255) NOT NULL DEFAULT " "');
        $this->alterColumn('user', 'last_name', 'varchar(255) NOT NULL DEFAULT " "');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'first_name', 'varchar(255) NOT NULL DEFAULT " "');
        $this->alterColumn('user', 'last_name', 'varchar(255) NOT NULL DEFAULT " "');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190927_122711_add_not_null_to_user cannot be reverted.\n";

        return false;
    }
    */
}
