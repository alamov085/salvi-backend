<?php

use yii\db\Migration;

/**
 * Class m190722_120636_add_row_to_table
 */
class m190722_120636_add_row_to_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'is_props_value', $this->boolean());
        $this->addColumn('category', 'is_value', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('category', 'is_props_value');
        $this->dropColumn('category', 'is_value');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190722_120636_add_row_to_table cannot be reverted.\n";

        return false;
    }
    */
}
