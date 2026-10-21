<?php

use yii\db\Migration;

/**
 * Class m191015_085925_add_to_order_coord
 */
class m191015_085925_add_to_order_coord extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_coordination', 'additional_type', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_coordination', 'additional_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191015_085925_add_to_order_coord cannot be reverted.\n";

        return false;
    }
    */
}
