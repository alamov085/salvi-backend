<?php

use yii\db\Migration;

/**
 * Class m190719_085758_add_row
 */
class m190719_085758_add_row extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order_coordination', 'user_id', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order_coordination', 'user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190719_085758_add_row cannot be reverted.\n";

        return false;
    }
    */
}
