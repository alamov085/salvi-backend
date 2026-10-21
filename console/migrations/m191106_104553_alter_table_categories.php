<?php

use yii\db\Migration;

/**
 * Class m191106_104553_alter_table_categories
 */
class m191106_104553_alter_table_categories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('category', 'markup', $this->decimal(6, 2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('category', 'markup', $this->decimal(6, 2)->defaultValue(0));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191106_104553_alter_table_categories cannot be reverted.\n";

        return false;
    }
    */
}
