<?php

use yii\db\Migration;

/**
 * Class m191018_073933_create_history
 */
class m191018_073933_create_history extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('history', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'email' => $this->string(),
            'role' => $this->string(),
            'entity' => $this->integer(),
            'entity_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191018_073933_create_history cannot be reverted.\n";

        return false;
    }
    */
}
