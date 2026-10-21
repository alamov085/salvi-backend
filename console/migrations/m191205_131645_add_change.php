<?php

use yii\db\Migration;

/**
 * Class m191205_131645_add_change
 */
class m191205_131645_add_change extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_product_history', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer(),
            'order_product_id' => $this->integer(),
            'from_uuid' => $this->string(),
            'to_uuid' => $this->string(),
            'user_id' => $this->integer(),
            'created_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order_product_history');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191205_131645_add_change cannot be reverted.\n";

        return false;
    }
    */
}
