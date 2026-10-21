<?php

use yii\db\Migration;

/**
 * Class m191021_095329_auth_history
 */
class m191021_095329_auth_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('auth_item_history', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'email' => $this->string(),
            'role' => $this->string(),
            'name' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('auth_item_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191021_095329_auth_history cannot be reverted.\n";

        return false;
    }
    */
}
