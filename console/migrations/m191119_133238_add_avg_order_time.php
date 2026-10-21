<?php

use yii\db\Migration;

/**
 * Class m191119_133238_add_avg_order_time
 */
class m191119_133238_add_avg_order_time extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'start_time', $this->integer(11)->notNull()->defaultValue(0));
        $this->addColumn('order', 'end_time', $this->integer(11)->notNull()->defaultValue(0));
        $this->addColumn('order', 'avg_time', $this->integer(11)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'start_time');
        $this->dropColumn('order', 'end_time');
        $this->dropColumn('order', 'avg_time');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191119_133238_add_avg_order_time cannot be reverted.\n";

        return false;
    }
    */
}
