<?php

use yii\db\Migration;

/**
 * Class m191007_121121_add_column_vendor
 */
class m191007_121121_add_column_vendor extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('vendor', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(),
            'name' => $this->string()
        ]);

        $this->createTable('attribute', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'category_uuid' => $this->string()
        ]);

        $this->createTable('attribute_value', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'attribute_id' => $this->integer()
        ]);

        $this->createTable('product_attribute', [
            'id' => $this->primaryKey(),
            'attribute_id' => $this->integer(),
            'value_id' => $this->integer(),
            'product_uuid' => $this->string()
        ]);

        $this->addColumn('product', 'vendor_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('vendor');
        $this->dropTable('attribute');
        $this->dropTable('attribute_value');
        $this->dropTable('product_attribute');
        $this->dropColumn('product', 'vendor_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191007_121121_add_column_vendor cannot be reverted.\n";

        return false;
    }
    */
}
