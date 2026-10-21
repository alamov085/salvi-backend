<?php

use yii\db\Migration;

/**
 * Class m191119_091522_add_status_to_product_spec
 */
class m191119_091522_add_status_to_product_spec extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specification_product', 'status', $this->integer()->notNull()->defaultValue(1));
        $this->addColumn('specification', 'location_id', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('specification_product', 'status');
        $this->dropColumn('specification', 'location_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191119_091522_add_status_to_product_spec cannot be reverted.\n";

        return false;
    }
    */
}
