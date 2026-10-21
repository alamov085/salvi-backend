<?php

use yii\db\Migration;

/**
 * Class m191225_145217_add_total_to_cart
 */
class m191225_145217_add_total_to_cart extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('cart', 'total', $this->decimal(16, 2)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('cart', 'total');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191225_145217_add_total_to_cart cannot be reverted.\n";

        return false;
    }
    */
}
