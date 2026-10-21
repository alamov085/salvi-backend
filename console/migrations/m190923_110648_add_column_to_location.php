<?php

use yii\db\Migration;

/**
 * Class m190923_110648_add_column_to_location
 */
class m190923_110648_add_column_to_location extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('locations', 'user_id_responsible', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('locations', 'user_id_responsible');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190923_110648_add_column_to_location cannot be reverted.\n";

        return false;
    }
    */
}
