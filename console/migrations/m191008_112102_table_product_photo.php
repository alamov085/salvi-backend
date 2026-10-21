<?php

use yii\db\Migration;

/**
 * Class m191008_112102_table_product_photo
 */
class m191008_112102_table_product_photo extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_photo', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'product_uuid' => $this->string(),
            'product_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('product_photo');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191008_112102_table_product_photo cannot be reverted.\n";

        return false;
    }
    */
}
