<?php

use yii\db\Migration;

/**
 * Class m190712_082724_add_column_to_company
 */
class m190712_082724_add_column_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'empty_string', $this->boolean()->defaultValue(false));
        $this->addColumn('company', 'budgets', $this->boolean()->defaultValue(false));
        $this->addColumn('company', 'non_budget_order', $this->boolean()->defaultValue(false));
        $this->addColumn('company', 'coordination', $this->boolean()->defaultValue(false));
        $this->addColumn('company', 'currency_id', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'empty_string');
        $this->dropColumn('company', 'budgets');
        $this->dropColumn('company', 'non_budget_order');
        $this->dropColumn('company', 'coordination');
        $this->dropColumn('company', 'currency_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190712_082724_add_column_to_company cannot be reverted.\n";

        return false;
    }
    */
}
