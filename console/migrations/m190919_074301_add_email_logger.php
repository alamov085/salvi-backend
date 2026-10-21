<?php

use yii\db\Migration;

/**
 * Class m190919_074301_add_email_logger
 */
class m190919_074301_add_email_logger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('email_log', [
            'id' => $this->primaryKey(),
            'email' => $this->string(),
            'body' => $this->text(),
            'result' => $this->string(),
            'created_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('email_log');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190919_074301_add_email_logger cannot be reverted.\n";

        return false;
    }
    */
}
