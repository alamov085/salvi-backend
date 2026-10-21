<?php

use yii\db\Migration;

/**
 * Class m191104_162245_add_markup_to_category
 */
class m191104_162245_add_markup_to_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'markup', $this->decimal(6, 2)->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('category', 'markup');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191104_162245_add_markup_to_category cannot be reverted.\n";

        return false;
    }
    */
}
