<?php

use yii\db\Migration;

/**
 * Class m190624_082103_user_all_location
 */
class m190624_082103_user_all_location extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'all_location', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'all_location');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190624_082103_user_all_location cannot be reverted.\n";

        return false;
    }
    */
}
