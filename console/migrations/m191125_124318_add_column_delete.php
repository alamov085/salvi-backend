<?php

use yii\db\Migration;

/**
 * Class m191125_124318_add_column_delete
 */
class m191125_124318_add_column_delete extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'delete', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product', 'delete');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191125_124318_add_column_delete cannot be reverted.\n";

        return false;
    }
    */
}
