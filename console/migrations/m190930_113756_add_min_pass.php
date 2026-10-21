<?php

use yii\db\Migration;

/**
 * Class m190930_113756_add_min_pass
 */
class m190930_113756_add_min_pass extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'password_difficult', 'integer NOT NULL DEFAULT 1');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'password_difficult', 'integer NOT NULL DEFAULT 1');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190930_113756_add_min_pass cannot be reverted.\n";

        return false;
    }
    */
}
