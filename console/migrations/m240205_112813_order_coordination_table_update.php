<?php

use yii\db\Migration;

/**
 * Class m240205_112813_order_coordination_table_update
 */
class m240205_112813_order_coordination_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('order_coordination', 'status_cancel', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240205_112813_order_coordination_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240205_112813_order_coordination_table_update cannot be reverted.\n";

        return false;
    }
    */
}
