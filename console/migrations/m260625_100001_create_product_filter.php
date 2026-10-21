<?php

use yii\db\Migration;

class m260625_100001_create_product_filter extends Migration
{
    public function safeUp()
    {
        $this->createTable('product_filter',[

            'id' => $this->primaryKey(),

            'product_id' => $this->integer()->notNull(),

            'filter_code' => $this->string()->notNull(),

            'filter_name' => $this->string()->notNull(),

            'filter_value_code' => $this->string()->notNull(),

            'filter_value' => $this->string()->notNull(),
        ]);

        $this->createIndex(
            'idx_pf_product',
            'product_filter',
            'product_id'

        );

        $this->createIndex(
            'idx_pf_filter_name',
            'product_filter',
            'filter_code'
        );


        $this->createIndex(
            'idx_pf_product_filter',
            'product_filter',
            [
                'product_id',
                'filter_code',
                'filter_value_code'
            ]
        );

        $this->createIndex(
            'idx_pf_filter',
            'product_filter',
            [
                'filter_code',
                'filter_value_code',
                'product_id'
            ]
        );



        $this->addForeignKey(
            'fk_pf_product',
            'product_filter',
            'product_id',
            'product',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(
            'fk_pf_product',
            'product_filter'
        );

        $this->dropIndex('idx_pf_product','product_filter');
        $this->dropIndex('idx_pf_filter','product_filter');
        $this->dropIndex('idx_pf_filter_name','product_filter');
        $this->dropTable('product_filter');
    }
}