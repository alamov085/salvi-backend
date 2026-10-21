<?php

use yii\db\Migration;

/**
 * Class m190812_075449_add_row_to_specification
 */
class m190812_075449_add_row_to_specification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('catalog_limitation', 'specification', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('catalog_limitation', 'specification');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190812_075449_add_row_to_specification cannot be reverted.\n";

        return false;
    }
    */
}
