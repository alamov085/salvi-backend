<?php

use yii\db\Migration;

/**
 * Class m190829_073347_add_colum_to_user
 */
class m190829_073347_add_colum_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'old_role', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'old_role');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190829_073347_add_colum_to_user cannot be reverted.\n";

        return false;
    }
    */
}
