<?php

use yii\db\Migration;

/**
 * Class m191017_111152_add_row_to_cost
 */
class m191017_111152_add_row_to_cost extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cost_center', 'uuid_client', $this->string());
        $this->addColumn('cost_center', 'uuid_contract', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cost_center', 'uuid_client');
        $this->dropColumn('cost_center', 'uuid_contract');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191017_111152_add_row_to_cost cannot be reverted.\n";

        return false;
    }
    */
}
