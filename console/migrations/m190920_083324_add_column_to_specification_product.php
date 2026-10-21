<?php

use yii\db\Migration;

/**
 * Class m190920_083324_add_column_to_specification_product
 */
class m190920_083324_add_column_to_specification_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specification_product', 'use_markup', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('specification_product', 'use_markup');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190920_083324_add_column_to_specification_product cannot be reverted.\n";

        return false;
    }
    */
}
