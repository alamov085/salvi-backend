<?php

use yii\db\Migration;

/**
 * Class m240205_104352_order_table_update
 */
class m240205_104352_order_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('order', 'one_day_shipment', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240205_104352_order_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240205_104352_order_table_update cannot be reverted.\n";

        return false;
    }
    */
}
