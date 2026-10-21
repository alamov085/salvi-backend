<?php

use yii\db\Migration;

/**
 * Class m260709_090850_product_filter_property
 */
class m260709_090850_product_filter_property extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_filter_property', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(255)->notNull(),
            'sort' => $this->integer()->notNull(),
            'slug' => $this->string(255)->notNull(),
        ]);

        $this->createIndex(
            'idx-product_filter_property-slug',
            'product_filter_property',
            'slug',
            true
        );

        $this->createIndex(
            'idx-product_filter_property-sort',
            'product_filter_property',
            'sort'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-product_filter_property-slug','product_filter_property');
        $this->dropIndex('idx-product_filter_property-sort','product_filter_property');
        $this->dropTable('product_filter_property');

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260709_090850_product_filter_property cannot be reverted.\n";

        return false;
    }
    */
}
