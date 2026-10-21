<?php

use yii\db\Migration;

/**
 * Class m190712_081341_product_category_table
 */
class m190712_081341_product_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_category', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(),
            'product_id' => $this->integer(),
            'category_uuid' => $this->string(),
            'product_uuid' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('product_category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190712_081341_product_category_table cannot be reverted.\n";

        return false;
    }
    */
}
