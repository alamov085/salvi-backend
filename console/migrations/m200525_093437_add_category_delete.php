<?php

use yii\db\Migration;

/**
 * Class m200525_093437_add_category_delete
 */
class m200525_093437_add_category_delete extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'delete', $this->smallInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('category', 'delete');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200525_093437_add_category_delete cannot be reverted.\n";

        return false;
    }
    */
}
