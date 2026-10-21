<?php

use yii\db\Migration;

/**
 * Class m251111_140350_update_contract_table
 */
class m251111_140350_update_contract_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contract', 'date_send_noty_end', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251111_140350_update_contract_table cannot be reverted.\n";

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251111_140350_update_contract_table cannot be reverted.\n";

        return false;
    }
    */
}
