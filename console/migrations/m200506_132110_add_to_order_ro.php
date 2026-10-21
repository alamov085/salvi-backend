<?php

use yii\db\Migration;

/**
 * Class m200506_132110_add_to_order_ro
 */
class m200506_132110_add_to_order_ro extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'ro_number', $this->string()->defaultValue(''));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'ro_number');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200506_132110_add_to_order_ro cannot be reverted.\n";

        return false;
    }
    */
}
