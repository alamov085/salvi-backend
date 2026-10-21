<?php

use yii\db\Migration;

/**
 * Class m191112_165657_add_index
 */
class m191112_165657_add_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('status_company_id', 'budget', ['status', 'company_id', 'id']);
        $this->createIndex('status_user_id_responsible', 'budget', ['user_id_responsible', 'status', 'company_id']);
        $this->createIndex('budget_id', 'budget_cost_center', ['budget_id']);
        $this->createIndex('budget_id', 'budget_location', ['budget_id']);
        $this->createIndex('location_id', 'budget_location', ['location_id']);
        $this->createIndex('entity', 'history', ['entity_id', 'entity']);
        $this->createIndex('user_manager', 'user', ['company_id', 'manager']);
        $this->createIndex('user_id', 'user_location', ['user_id']);
        $this->createIndex('user_id', 'user_cost_center', ['user_id']);
        $this->createIndex('company_id', 'user_company', ['company_id']);
        $this->createIndex('user_id', 'user_company', ['user_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('status_company_id', 'budget');
        $this->dropIndex('status_user_id_responsible', 'budget');
        $this->dropIndex('budget_id', 'budget_cost_center');
        $this->dropIndex('budget_id', 'budget_location');
        $this->dropIndex('location_id', 'budget_location');
        $this->dropIndex('entity', 'history');
        $this->dropIndex('user_manager', 'user');
        $this->dropIndex('user_id', 'user_location');
        $this->dropIndex('user_id', 'user_cost_center');
        $this->dropIndex('company_id', 'user_company');
        $this->dropIndex('user_id', 'user_company');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191112_165657_add_index cannot be reverted.\n";

        return false;
    }
    */
}
