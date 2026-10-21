<?php

use yii\db\Migration;

/**
 * Class m190927_115410_add_company_id_value
 */
class m190927_115410_add_company_id_value extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('specification', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('order', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('locations', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('coordination', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('contract', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('company_entity_location', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('company_entity', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('catalog_limitation', 'company_id', 'integer NOT NULL DEFAULT 0');
        $this->alterColumn('budget', 'company_id', 'integer NOT NULL DEFAULT 0');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'company_id', 'integer NOT NULL');
        $this->alterColumn('specification', 'company_id', 'integer NOT NULL');
        $this->alterColumn('order', 'company_id', 'integer NOT NULL');
        $this->alterColumn('locations', 'company_id', 'integer NOT NULL');
        $this->alterColumn('coordination', 'company_id', 'integer NOT NULL');
        $this->alterColumn('contract', 'company_id', 'integer NOT NULL');
        $this->alterColumn('company_entity_location', 'company_id', 'integer NOT NULL');
        $this->alterColumn('company_entity', 'company_id', 'integer NOT NULL');
        $this->alterColumn('catalog_limitation', 'company_id', 'integer NOT NULL');
        $this->alterColumn('budget', 'company_id', 'integer NOT NULL');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190927_115410_add_company_id_value cannot be reverted.\n";

        return false;
    }
    */
}
