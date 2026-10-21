<?php

use yii\db\Migration;

/**
 * Class m241120_120208_user_table_update
 */
class m241120_120208_user_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $this->addColumn('user', 'is_sending_mail', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241120_120208_user_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241120_120208_user_table_update cannot be reverted.\n";

        return false;
    }
    */
}
