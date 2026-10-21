<?php

use yii\db\Migration;

/**
 * Class m190711_141427_category_table
 */
class m190711_141427_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('category', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(),
            'name' => $this->string(),
            'is_view' => $this->boolean(),
            'is_props' => $this->boolean(),
            'parent_uuid' => $this->string(),
            'parent_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190711_141427_category_table cannot be reverted.\n";

        return false;
    }
    */
}
