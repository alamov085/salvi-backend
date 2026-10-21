<?php

use yii\db\Migration;

/**
 * Class m190523_125944_company
 */
class m190523_125944_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('company', [
            'id' => $this->primaryKey(),
            'type' => $this->smallInteger()->defaultValue(1),
            'short_name' => $this->string(),
            'full_name' => $this->string(),
            'address' => $this->string(),
            'edrpou' => $this->string(),
            'inn' => $this->string(),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('company');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190523_125944_company cannot be reverted.\n";

        return false;
    }
    */
}
