<?php

use yii\db\Migration;

/**
 * Class m200206_120045_add_to_table_product
 */
class m200206_120045_add_to_table_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'is_buffer', $this->boolean()->defaultValue(false));
        $this->addColumn('product', 'buffer_status', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product', 'is_buffer');
        $this->dropColumn('product', 'buffer_status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200206_120045_add_to_table_product cannot be reverted.\n";

        return false;
    }
    */
}
