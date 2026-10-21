<?php

use yii\db\Migration;

/**
 * Class m191028_090040_add_column_to_order
 */
class m191028_090040_add_column_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'modal', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'modal');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191028_090040_add_column_to_order cannot be reverted.\n";

        return false;
    }
    */
}
