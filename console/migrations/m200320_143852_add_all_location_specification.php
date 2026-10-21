<?php

use yii\db\Migration;

/**
 * Class m200320_143852_add_all_location_specification
 */
class m200320_143852_add_all_location_specification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('specification', 'all_location', $this->boolean()->defaultValue(0));
        $this->dropColumn('specification', 'location_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('specification', 'all_location');
        $this->addColumn('specification', 'location_id', $this->integer()->defaultValue(0));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200320_143852_add_all_location_specification cannot be reverted.\n";

        return false;
    }
    */
}
