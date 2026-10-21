<?php

use yii\db\Migration;

/**
 * Class m200429_061306_add_product_updated_at
 */
class m200429_061306_add_product_updated_at extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'updated_at', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product', 'updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200429_061306_add_product_updated_at cannot be reverted.\n";

        return false;
    }
    */
}
