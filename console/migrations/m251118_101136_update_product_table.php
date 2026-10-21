<?php

use yii\db\Migration;

/**
 * Class m251118_101136_update_product_table
 */
class m251118_101136_update_product_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'is_season_goods', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251118_101136_update_product_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251118_101136_update_product_table cannot be reverted.\n";

        return false;
    }
    */
}
