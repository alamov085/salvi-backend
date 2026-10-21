<?php

use yii\db\Migration;

/**
 * Class m191007_150648_add_column_to_auth_item
 */
class m191007_150648_add_column_to_auth_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('auth_item', 'default_role', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('auth_item', 'default_role');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191007_150648_add_column_to_auth_item cannot be reverted.\n";

        return false;
    }
    */
}
