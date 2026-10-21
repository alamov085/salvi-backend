<?php

use yii\db\Migration;

/**
 * Class m191016_123141_add_column_to_contract
 */
class m191016_123141_add_column_to_contract extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contract', 'all_location', $this->smallInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contract', 'all_location');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191016_123141_add_column_to_contract cannot be reverted.\n";

        return false;
    }
    */
}
