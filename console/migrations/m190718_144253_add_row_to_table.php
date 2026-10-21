<?php

use yii\db\Migration;

/**
 * Class m190718_144253_add_row_to_table
 */
class m190718_144253_add_row_to_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'coordination', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'coordination');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190718_144253_add_row_to_table cannot be reverted.\n";

        return false;
    }
    */
}
