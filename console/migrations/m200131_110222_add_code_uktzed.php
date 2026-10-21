<?php

use yii\db\Migration;

/**
 * Class m200131_110222_add_code_uktzed
 */
class m200131_110222_add_code_uktzed extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('product_uktzed', [
            'id' => $this->primaryKey(),
            'full_name' => $this->string(),
            'short_name' => $this->string(),
            'uuid' => $this->string(),
            'type' => $this->integer()
        ]);
        $this->createTable('product_brand', [
            'id' => $this->primaryKey(),
            'uuid' => $this->string(),
            'name' => $this->string(),
            'manufacture_id' => $this->integer()
        ]);
        $this->addColumn('product', 'brand_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('product_uktzed');
        $this->dropTable('product_brand');
        $this->dropColumn('product', 'brand_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_110222_add_code_uktzed cannot be reverted.\n";

        return false;
    }
    */
}
