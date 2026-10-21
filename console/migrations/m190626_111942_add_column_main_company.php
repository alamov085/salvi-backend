<?php

use yii\db\Migration;

/**
 * Class m190626_111942_add_column_main_company
 */
class m190626_111942_add_column_main_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company_entity', 'is_main', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company_entity', 'is_main');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190626_111942_add_column_main_company cannot be reverted.\n";

        return false;
    }
    */
}
