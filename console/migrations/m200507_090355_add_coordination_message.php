<?php

use yii\db\Migration;

/**
 * Class m200507_090355_add_coordination_message
 */
class m200507_090355_add_coordination_message extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('coordination', 'enable_notification', $this->boolean()->defaultValue(0));
        $this->addColumn('order_coordination', 'enable_notification', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('coordination', 'enable_notification');
        $this->dropColumn('order_coordination', 'enable_notification');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200507_090355_add_coordination_message cannot be reverted.\n";

        return false;
    }
    */
}
