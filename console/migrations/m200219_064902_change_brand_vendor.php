<?php

use yii\db\Migration;

/**
 * Class m200219_064902_change_brand_vendor
 */
class m200219_064902_change_brand_vendor extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('product_brand', 'manufacture_id');
        $this->addColumn('product_brand', 'vendor_uuid', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('product_brand', 'manufacture_id', $this->integer());
        $this->dropColumn('product_brand', 'vendor_uuid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200219_064902_change_brand_vendor cannot be reverted.\n";

        return false;
    }
    */
}
