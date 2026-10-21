<?php

use yii\db\Migration;

/**
 * Class m200706_134553_add_column_to_company
 */
class m200706_134553_add_column_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'po_number_required', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'po_number_required');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200706_134553_add_column_to_company cannot be reverted.\n";

        return false;
    }
    */
}
