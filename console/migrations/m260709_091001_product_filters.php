<?php

use yii\db\Migration;

/**
 * Class m260709_091001_product_filters
 */
class m260709_091001_product_filters extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_filters', [
            'product_id' => $this->integer()->notNull(),
            'property_id' => $this->bigInteger()->notNull(),
            'value_id' => $this->bigInteger()->notNull(),
        ]);

        $this->addPrimaryKey(
            'pk-product_filters',
            'product_filters',
            ['product_id', 'property_id', 'value_id']
        );

        $this->createIndex(
            'idx-product_filter-product',
            'product_filters',
            'product_id'
        );

        $this->createIndex(
            'idx-product_filter-property',
            'product_filters',
            'property_id'
        );

        $this->createIndex(
            'idx-product_filter-value',
            'product_filters',
            'value_id'
        );

        // Очень важный индекс для фильтрации
        $this->createIndex(
            'idx-product_filter-property_value_product',
            'product_filters',
            ['property_id', 'value_id', 'product_id']
        );

        $this->addForeignKey(
            'fk-product_filter-product',
            'product_filters',
            'product_id',
            'product',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-product_filter-property',
            'product_filters',
            'property_id',
            'product_filter_property',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-product_filter-value',
            'product_filters',
            'value_id',
            'product_filter_value',
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
            'fk-product_filter-product',
            'product_filters'
        );

        $this->dropForeignKey(
            'fk-product_filter-property',
            'product_filters'
        );

        $this->dropForeignKey(
            'fk-product_filter-value',
            'product_filters'
        );

        $this->dropPrimaryKey(
            'pk-product_filters',
            'product_filters'
        );

        $this->dropIndex('idx-product_filter-property_value_product','product_filters');
        $this->dropIndex('idx-product_filter-value','product_filters');
        $this->dropIndex('idx-product_filter-property','product_filters');
        $this->dropIndex('idx-product_filter-product','product_filters');
        $this->dropTable('product_filters');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260709_091001_product_filters cannot be reverted.\n";

        return false;
    }
    */
}
