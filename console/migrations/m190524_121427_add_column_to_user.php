<?php

use yii\db\Migration;

/**
 * Class m190524_121427_add_column_to_user
 */
class m190524_121427_add_column_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'company_id', $this->integer());
        $this->addColumn('user', 'first_name', $this->string());
        $this->addColumn('user', 'last_name', $this->string());
        $this->addColumn('user', 'phone_number', $this->integer(9));
        $this->addColumn('user', 'position', $this->string());
        $this->addColumn('user', 'currency_id', $this->integer());
        $this->addColumn('user', 'location_id', $this->integer());
        $this->addColumn('user', 'password_difficult', $this->integer());
        $this->addColumn('user', 'reason', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'company_id');
        $this->dropColumn('user', 'first_name');
        $this->dropColumn('user', 'last_name');
        $this->dropColumn('user', 'phone_number');
        $this->dropColumn('user', 'position');
        $this->dropColumn('user', 'currency_id');
        $this->dropColumn('user', 'location_id');
        $this->dropColumn('user', 'password_difficult');
        $this->dropColumn('user', 'reason');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190524_121427_add_column_to_user cannot be reverted.\n";

        return false;
    }
    */
}
