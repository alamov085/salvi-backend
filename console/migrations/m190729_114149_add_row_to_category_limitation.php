<?php

use yii\db\Migration;

/**
 * Class m190729_114149_add_row_to_category_limitation
 */
class m190729_114149_add_row_to_category_limitation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('catalog_limitation', 'total_product', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('catalog_limitation', 'total_product');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190729_114149_add_row_to_category_limitation cannot be reverted.\n";

        return false;
    }
    */
}
