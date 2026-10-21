<?php

use yii\db\Migration;

/**
 * Class m191106_094408_add_config
 */
class m191106_094408_add_config extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('config', [
            'id' => $this->primaryKey(),
            'params' => $this->integer(),
            'value' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('config');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191106_094408_add_config cannot be reverted.\n";

        return false;
    }
    */
}
