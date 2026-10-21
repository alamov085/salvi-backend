<?php

use yii\db\Migration;

/**
 * Class m211125_072159_modify_categories_table
 */
class m211125_072159_modify_categories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'color', $this->string()->null());
        $this->addColumn('category', 'sort', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m211125_072159_modify_categories_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211125_072159_modify_categories_table cannot be reverted.\n";

        return false;
    }
    */
}
