<?php

use yii\db\Migration;

/**
 * Class m191202_104808_add_column_to_budget
 */
class m191202_104808_add_column_to_budget extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('budget', 'all_location', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('budget', 'all_location');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191202_104808_add_column_to_budget cannot be reverted.\n";

        return false;
    }
    */
}
