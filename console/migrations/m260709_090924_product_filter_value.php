<?php

use yii\db\Migration;

/**
 * Class m260709_090924_product_filter_value
 */
class m260709_090924_product_filter_value extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_filter_value', [
            'id' => $this->bigPrimaryKey(),
            'property_id' => $this->bigInteger()->notNull(),
            'name' => $this->string(255)->notNull(),
            'sort' => $this->integer()->notNull(),
            'slug' => $this->string(255)->notNull(),
        ]);

        $this->createIndex(
            'idx-product_filter_value-property_id',
            'product_filter_value',
            'property_id'
        );

        $this->createIndex(
            'idx-product_filter_value-sort',
            'product_filter_value',
            'sort'
        );

        $this->createIndex(
            'idx-product_filter_value-property_slug',
            'product_filter_value',
            ['property_id', 'slug'],
            true
        );

        $this->addForeignKey(
            'fk-product_filter_value-property_id',
            'product_filter_value',
            'property_id',
            'product_filter_property',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-product_filter_value-property_id',
            'product_filter_value'
        );


        $this->dropIndex('idx-product_filter_value-property_id','product_filter_value');
        $this->dropIndex('idx-product_filter_value-property_slug','product_filter_value');
        $this->dropTable('product_filter_value');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260709_090924_product_filter_value cannot be reverted.\n";

        return false;
    }
    */
}
