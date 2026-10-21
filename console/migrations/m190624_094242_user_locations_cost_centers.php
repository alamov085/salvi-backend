<?php

use yii\db\Migration;

/**
 * Class m190624_094242_user_locations_cost_centers
 */
class m190624_094242_user_locations_cost_centers extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_location', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'location_id' => $this->integer()
        ]);

        $this->createTable('user_cost_center', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'cost_center_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_location');
        $this->dropTable('user_cost_center');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190624_094242_user_locations_cost_centers cannot be reverted.\n";

        return false;
    }
    */
}
