<?php

use yii\db\Migration;

/**
 * Class m190729_090214_add_row_to_company_entity
 */
class m190729_090214_add_row_to_company_entity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company_entity', 'status_check', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company_entity', 'status_check');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190729_090214_add_row_to_company_entity cannot be reverted.\n";

        return false;
    }
    */
}
