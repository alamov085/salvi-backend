<?php

use yii\db\Migration;

/**
 * Class m191007_152212_add_vat_to_product
 */
class m191007_152212_add_vat_to_product extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'vat', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product', 'vat');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191007_152212_add_vat_to_product cannot be reverted.\n";

        return false;
    }
    */
}
