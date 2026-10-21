<?php

use yii\db\Migration;

/**
 * Class m190802_142850_add_column_to_company
 */
class m190802_142850_add_column_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'specification', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'specification');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190802_142850_add_column_to_company cannot be reverted.\n";

        return false;
    }
    */
}
