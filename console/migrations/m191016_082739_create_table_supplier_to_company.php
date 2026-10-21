<?php

use yii\db\Migration;

/**
 * Class m191016_082739_create_table_supplier_to_company
 */
class m191016_082739_create_table_supplier_to_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('company_supplier', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'supplier_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('company_supplier');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191016_082739_create_table_supplier_to_company cannot be reverted.\n";

        return false;
    }
    */
}
