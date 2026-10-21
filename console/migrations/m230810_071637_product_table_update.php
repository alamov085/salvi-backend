<?php

use yii\db\Migration;

/**
 * Class m230810_071637_product_table_update
 */
class m230810_071637_product_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->addColumn('product', 'leftovers', $this->integer()->defaultValue(999));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230810_071637_product_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230810_071637_product_table_update cannot be reverted.\n";

        return false;
    }
    */
}
