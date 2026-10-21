<?php

use yii\db\Migration;

/**
 * Class m200324_090601_change_phone
 */
class m200324_090601_change_phone extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('locations', 'phone_one', $this->string(20));
        $this->alterColumn('locations', 'phone_two', $this->string(20));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('locations', 'phone_one', $this->integer(12));
        $this->alterColumn('locations', 'phone_two', $this->integer(12));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200324_090601_change_phone cannot be reverted.\n";

        return false;
    }
    */
}
