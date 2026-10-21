<?php

use yii\db\Migration;

/**
 * Class m201229_072524_rename_delete_column_product
 */
class m201229_072524_rename_delete_column_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('product', 'delete', 'deleted');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('product', 'deleted', 'delete');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201229_072524_rename_delete_column_product cannot be reverted.\n";

        return false;
    }
    */
}
