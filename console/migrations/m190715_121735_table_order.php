<?php

use yii\db\Migration;

/**
 * Class m190715_121735_table_order
 */
class m190715_121735_table_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'delivery_date' => $this->integer(),
            'company_entity_id' => $this->integer(),
            'location_id' => $this->integer(),
            'cost_center_id' => $this->integer(),
            'contract_id' => $this->integer(),
            'budget_id' => $this->integer(),
            'comment' => $this->string(),
            'created_by_user' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'status' => $this->integer(),
            'total_sum' => $this->decimal(16, 2)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190715_121735_table_order cannot be reverted.\n";

        return false;
    }
    */
}
