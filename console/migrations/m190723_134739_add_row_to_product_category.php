<?php

use yii\db\Migration;

/**
 * Class m190723_134739_add_row_to_product_category
 */
class m190723_134739_add_row_to_product_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product_category', 'is_main', $this->boolean());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('product_category', 'is_main');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190723_134739_add_row_to_product_category cannot be reverted.\n";

        return false;
    }
    */
}
