<?php

use yii\db\Migration;

/**
 * Class m220110_094259_add_code_field_to_categories
 */
class m220110_094259_add_code_field_to_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'code', $this->string()->null()->unique());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m220110_094259_add_code_field_to_categories cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220110_094259_add_code_field_to_categories cannot be reverted.\n";

        return false;
    }
    */
}
