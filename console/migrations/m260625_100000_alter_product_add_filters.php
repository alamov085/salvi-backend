<?php
use yii\db\Migration;

class m260625_100000_alter_product_add_filters extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('product','brand_id');

        $this->addColumn(
            'product',
            'brand_id',
            $this->integer()->null()
        );

        $this->addForeignKey(
            'fk_product_brand',
            'product',
            'brand_id',
            'brand',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->createIndex(
            'idx-product-brand',
            'product',
            'brand_id'
        );


    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-product-brand', 'product');

        $this->dropIndex('idx-product-brand','product');

        $this->dropColumn('product','brand_id');
    }
}