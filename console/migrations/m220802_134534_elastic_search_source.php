<?php

use yii\db\Migration;

/**
 * Class m220802_134534_elastic_search_source
 */
class m220802_134534_elastic_search_source extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('elastic_search_source', [
            'uuid' =>  $this->string(),
            'name' =>  $this->string(),
            'description' =>  $this->text(),
            'unit_id' =>  $this->integer(),
            'unit_uuid' =>  $this->string(),
            'brand_id' =>  $this->integer(),
            'deleted' =>  $this->integer(),
            'uktzed' =>  $this->string(),
            'brand_name' =>  $this->string(),
            'category_id' =>  $this->integer(),
            'category_uuid' =>  $this->string(),
            'category_name' =>  $this->string(),
            'attribute_id' =>  $this->integer(),
            'value_id' =>  $this->integer(),
            'attribute_name' =>  $this->string(),
            'attribute_value' =>  $this->string(),
            'full_name' =>  $this->string(),
            'short_name' =>  $this->string(),
            'product_image' =>  $this->string(),
            'price' =>  $this->string(),
            'product_id' =>  $this->integer(),
            'unit_name' =>  $this->string(),
            'product_synonym' =>  $this->text(),
            'sku' =>  $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('elastic_search_source');

//        echo "m220802_134534_elastic_search_source cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220802_134534_elastic_search_source cannot be reverted.\n";

        return false;
    }
    */
}
