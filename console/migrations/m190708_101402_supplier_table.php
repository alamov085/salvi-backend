<?php

use yii\db\Migration;

/**
 * Class m190708_101402_supplier_table
 */
class m190708_101402_supplier_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('supplier', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'edrpou' => $this->string(),
            'inn' => $this->string(),
            'description' => $this->text(),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('supplier');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190708_101402_supplier_table cannot be reverted.\n";

        return false;
    }
    */
}
