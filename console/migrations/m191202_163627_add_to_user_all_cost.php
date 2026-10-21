<?php

use yii\db\Migration;

/**
 * Class m191202_163627_add_to_user_all_cost
 */
class m191202_163627_add_to_user_all_cost extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'all_cost', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'all_cost');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191202_163627_add_to_user_all_cost cannot be reverted.\n";

        return false;
    }
    */
}
