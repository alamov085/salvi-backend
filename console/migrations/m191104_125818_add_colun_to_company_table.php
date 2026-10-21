<?php

use yii\db\Migration;

/**
 * Class m191104_125818_add_colun_to_company_table
 */
class m191104_125818_add_colun_to_company_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'new_category', $this->boolean()->defaultValue(1));
        $this->addColumn('company', 'max_category_id', $this->integer()->defaultValue(0));
        $this->alterColumn('company', 'catalog_limitation', 'integer NOT NULL DEFAULT 1');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'new_category');
        $this->dropColumn('company', 'max_category_id');
        $this->alterColumn('company', 'catalog_limitation', 'integer NOT NULL DEFAULT 0');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191104_125818_add_colun_to_company_table cannot be reverted.\n";

        return false;
    }
    */
}
