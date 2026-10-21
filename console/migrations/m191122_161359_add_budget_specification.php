<?php

use yii\db\Migration;

/**
 * Class m191122_161359_add_budget_specification
 */
class m191122_161359_add_budget_specification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('budget_specification', [
            'id' => $this->primaryKey(),
            'budget_id' => $this->integer(),
            'specification_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('budget_specification');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191122_161359_add_budget_specification cannot be reverted.\n";

        return false;
    }
    */
}
