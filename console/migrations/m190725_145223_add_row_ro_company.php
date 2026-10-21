<?php

use yii\db\Migration;

/**
 * Class m190725_145223_add_row_ro_company
 */
class m190725_145223_add_row_ro_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('company', 'catalog_limitation', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('company', 'catalog_limitation');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190725_145223_add_row_ro_company cannot be reverted.\n";

        return false;
    }
    */
}
