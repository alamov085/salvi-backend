<?php

use yii\db\Migration;

/**
 * Class m190620_063334_add_column_to_auth_item
 */
class m190620_063334_add_column_to_auth_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('auth_item', 'company_id', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('auth_item', 'company_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190620_063334_add_column_to_auth_item cannot be reverted.\n";

        return false;
    }
    */
}
