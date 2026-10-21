<?php

use yii\db\Migration;

/**
 * Class m190626_121044_add_column_show_catalog
 */
class m190626_121044_add_column_show_catalog extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'show_catalog', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'show_catalog');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190626_121044_add_column_show_catalog cannot be reverted.\n";

        return false;
    }
    */
}
