<?php

use yii\db\Migration;

/**
 * Class m190711_150720_add_table_unit
 */
class m190711_150720_add_table_unit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('unit', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(),
            'name' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('unit');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190711_150720_add_table_unit cannot be reverted.\n";

        return false;
    }
    */
}
