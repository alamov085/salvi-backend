<?php

use yii\db\Migration;

/**
 * Class m200203_075641_admin_emails
 */
class m200203_075641_admin_emails extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('admin_email', [
            'id' => $this->primaryKey(),
            'email' => $this->string(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('admin_email');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200203_075641_admin_emails cannot be reverted.\n";

        return false;
    }
    */
}
