<?php

use yii\db\Migration;

/**
 * Class m190712_112615_contract_table
 */
class m190712_112615_contract_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contract', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'name' => $this->string(),
            'number' => $this->string(),
            'supplier_id' => $this->integer(),
            'company_entity_id' => $this->integer(),
            'date_start' => $this->integer(),
            'date_end' => $this->integer(),
            'location_id' => $this->integer(),
            'comment' => $this->text(),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('contract_cost_center', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer(),
            'cost_center_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contract');
        $this->dropTable('contract_cost_center');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190712_112615_contract_table cannot be reverted.\n";

        return false;
    }
    */
}
