<?php

use yii\db\Migration;

/**
 * Class m190712_080535_table_product
 */
class m190712_080535_table_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(),
            'sku' => $this->string(),
            'uktzed' => $this->string(),
            'buy_price' => $this->decimal(10, 2),
            'name' => $this->string(),
            'description' => $this->text(),
            'unit_uuid' => $this->string(),
            'unit_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190712_080535_table_product cannot be reverted.\n";

        return false;
    }
    */
}
