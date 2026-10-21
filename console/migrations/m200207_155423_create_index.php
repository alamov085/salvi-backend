<?php

use yii\db\Migration;

/**
 * Class m200207_155423_create_index
 */
class m200207_155423_create_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('budget_id', 'order', 'budget_id');
        $this->createIndex('company_status', 'budget', ['company_id', 'status']);
        $this->createIndex('status', 'budget', 'status');
        $this->createIndex('category_uuid_company_id', 'catalog_limitation', ['category_uuid', 'company_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('budget_id', 'order');
        $this->dropIndex('company_status', 'budget');
        $this->dropIndex('status', 'budget');
        $this->dropIndex('category_uuid_company_id', 'catalog_limitation');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200207_155423_create_index cannot be reverted.\n";

        return false;
    }
    */
}
