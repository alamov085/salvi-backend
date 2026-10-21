<?php

use yii\db\Migration;

/**
 * Class m190612_093741_add_status_to_company
 */
class m190612_093741_add_status_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'status', $this->integer()->defaultValue(2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190612_093741_add_status_to_company cannot be reverted.\n";

        return false;
    }
    */
}
