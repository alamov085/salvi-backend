<?php
use yii\db\Migration;

class m260625_130000_alter_category_add_path extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            'category',
            'full_path',
            $this->string()
        );

        $this->addColumn(
            'category',
            'level',
            $this->integer()->defaultValue(0)
        );

        $this->createIndex(
            'idx_level_category',
            'category',
            'level'
        );


        $this->createIndex(
            'idx_cart_order_company',
            'cart',
            [
                'order_id',
                'company_id',
            ]
        );

        $this->createIndex(
            'idx_cart_order_company_user_id',
            'cart',
            [
                'order_id',
                'company_id',
                'user_id',
            ]
        );

        $this->createIndex(
            'idx_cart',
            'cart_product_history',
            'cart_id'
        );

    }

    public function safeDown()
    {
        $this->dropIndex('idx_level_category','category');

        $this->dropIndex('idx_cart_order_company','cart');
        $this->dropIndex('idx_cart_order_company_user_id','cart');

        $this->dropIndex('idx_cart','cart_product_history');

        $this->dropColumn('category','full_path');
        $this->dropColumn('category','level');
    }
}