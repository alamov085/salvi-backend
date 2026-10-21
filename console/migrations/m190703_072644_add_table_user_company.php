<?php

use yii\db\Migration;

/**
 * Class m190703_072644_add_table_user_company
 */
class m190703_072644_add_table_user_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_company', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'company_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user_company');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190703_072644_add_table_user_company cannot be reverted.\n";

        return false;
    }
    */
}
