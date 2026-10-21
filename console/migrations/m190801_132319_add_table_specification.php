<?php

use yii\db\Migration;

/**
 * Class m190801_132319_add_table_specification
 */
class m190801_132319_add_table_specification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('specification', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'name' => $this->string(),
            'contract_id' => $this->integer(),
            'date_start' => $this->integer(),
            'date_end' => $this->integer(),
            'status' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('specification_location', [
            'id' => $this->primaryKey(),
            'specification_id' => $this->integer(),
            'location_id' => $this->integer()
        ]);

        $this->createTable('specification_product', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'product_uuid' => $this->string(),
            'specification_id' => $this->integer(),
            'price' => $this->decimal(10, 2)->defaultValue(0),
            'markup' => $this->decimal(5, 2)->defaultValue(0),
            'manual_markup' => $this->smallInteger()->defaultValue(0)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('specification');
        $this->dropTable('specification_location');
        $this->dropTable('specification_product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190801_132319_add_table_specification cannot be reverted.\n";

        return false;
    }
    */
}
