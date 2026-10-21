<?php

use yii\db\Migration;

/**
 * Class m190723_111349_add_row_to_category
 */
class m190723_111349_add_row_to_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'has_child', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('category', 'has_child');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190723_111349_add_row_to_category cannot be reverted.\n";

        return false;
    }
    */
}
