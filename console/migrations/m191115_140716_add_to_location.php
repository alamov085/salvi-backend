<?php

use yii\db\Migration;

/**
 * Class m191115_140716_add_to_location
 */
class m191115_140716_add_to_location extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('locations', 'lat', $this->string());
        $this->addColumn('locations', 'lng', $this->string());
        $this->addColumn('locations', 'region', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('locations', 'lat');
        $this->dropColumn('locations', 'lng');
        $this->dropColumn('locations', 'region');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191115_140716_add_to_location cannot be reverted.\n";

        return false;
    }
    */
}
