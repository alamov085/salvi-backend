<?php

use yii\db\Migration;

/**
 * Class m190701_144614_add_table_budget
 */
class m190701_144614_add_table_budget extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('budget', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'name' => $this->string(20),
            'sum' => $this->decimal(22, 2),
            'date_start' => $this->integer(11),
            'date_end' => $this->integer(11),
            'budget_restriction' => $this->smallInteger(),
            'all_cost' => $this->smallInteger(),
            'user_id_responsible' => $this->integer(),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11)
        ]);
        $this->createTable('budget_location', [
            'id' => $this->primaryKey(),
            'budget_id' => $this->integer(),
            'location_id' => $this->integer()
        ]);
        $this->createTable('budget_cost_center', [
            'id' => $this->primaryKey(),
            'budget_id' => $this->integer(),
            'cost_center_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('budget');
        $this->dropTable('budget_location');
        $this->dropTable('budget_cost_center');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190701_144614_add_table_budget cannot be reverted.\n";

        return false;
    }
    */
}
