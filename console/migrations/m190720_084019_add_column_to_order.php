<?php

use yii\db\Migration;

/**
 * Class m190720_084019_add_column_to_order
 */
class m190720_084019_add_column_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'empty_string', $this->smallInteger()->defaultValue(0));
        $this->addColumn('order', 'budgets', $this->smallInteger()->defaultValue(0));
        $this->addColumn('order', 'non_budget_order', $this->smallInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'empty_string');
        $this->dropColumn('order', 'budgets');
        $this->dropColumn('order', 'non_budget_order');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190720_084019_add_column_to_order cannot be reverted.\n";

        return false;
    }
    */
}
