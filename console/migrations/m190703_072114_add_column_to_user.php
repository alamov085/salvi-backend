<?php

use yii\db\Migration;

/**
 * Class m190703_072114_add_column_to_user
 */
class m190703_072114_add_column_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'manager', $this->integer()->defaultValue(0));
        $this->addColumn('user', 'all_company', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'manager');
        $this->dropColumn('user', 'all_company');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190703_072114_add_column_to_user cannot be reverted.\n";

        return false;
    }
    */
}
