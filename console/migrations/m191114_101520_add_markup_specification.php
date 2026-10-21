<?php

use yii\db\Migration;

/**
 * Class m191114_101520_add_markup_specification
 */
class m191114_101520_add_markup_specification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specification', 'markup', $this->decimal(6, 2)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('specification', 'markup');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191114_101520_add_markup_specification cannot be reverted.\n";

        return false;
    }
    */
}
