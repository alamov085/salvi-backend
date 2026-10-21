<?php

use yii\db\Migration;

/**
 * Class m250624_123826_user_table_update
 */
class m250624_123826_user_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'is_need_change_password', $this->boolean()->defaultValue(true));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250624_123826_user_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250624_123826_user_table_update cannot be reverted.\n";

        return false;
    }
    */
}
