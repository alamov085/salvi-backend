<?php

use yii\db\Migration;

/**
 * Class m210927_080149_add_sort_field_to_products
 */
class m210927_080149_add_sort_field_to_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'sort', $this->integer()->defaultValue(null));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210927_080149_add_sort_field_to_products cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210927_080149_add_sort_field_to_products cannot be reverted.\n";

        return false;
    }
    */
}
