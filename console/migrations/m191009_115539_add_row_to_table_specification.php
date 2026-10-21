<?php

use yii\db\Migration;

/**
 * Class m191009_115539_add_row_to_table_specification
 */
class m191009_115539_add_row_to_table_specification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specification_product', 'quantity', $this->integer()->defaultValue(-1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('specification_product', 'quantity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191009_115539_add_row_to_table_specification cannot be reverted.\n";

        return false;
    }
    */
}
