<?php

use yii\db\Migration;

/**
 * Class m190617_110457_add_table_locations
 */
class m190617_110457_add_table_locations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('locations', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->defaultValue(0),
            'name' => $this->string(),
            'zip_code' => $this->integer(),
            'city' => $this->string(),
            'street' => $this->string(),
            'building' => $this->string(),
            'office' => $this->string(),
            'floor'=>$this->integer(),
            'phone_one' => $this->integer(12),
            'phone_two' => $this->integer(12),
            'comment'=>$this->string(),
            'isActive' => $this->integer()->defaultValue(0),
            'activeDate' => $this->integer(),
            'budgetLocationOnly' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('locations');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190617_110457_add_table_locations cannot be reverted.\n";

        return false;
    }
    */
}
