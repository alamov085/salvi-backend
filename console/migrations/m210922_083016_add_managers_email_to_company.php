<?php

use yii\db\Migration;

/**
 * Class m210922_083016_add_managers_email_to_company
 */
class m210922_083016_add_managers_email_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'manager_email', $this->string()->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210922_083016_add_managers_email_to_company cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210922_083016_add_managers_email_to_company cannot be reverted.\n";

        return false;
    }
    */
}
