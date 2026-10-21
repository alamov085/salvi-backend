<?php

use yii\db\Migration;

/**
 * Class m250224_121222_product_table_update
 */
class m250224_121222_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('product', 'is_temperature_conditions', $this->boolean()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250224_121222_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250224_121222_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
