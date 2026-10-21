<?php

use yii\db\Migration;

/**
 * Class m241218_130134_product_table_update
 */
class m241218_130134_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('product', 'strict_multiplicity', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241218_130134_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241218_130134_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
