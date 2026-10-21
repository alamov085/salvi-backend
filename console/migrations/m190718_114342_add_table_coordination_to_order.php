<?php

use yii\db\Migration;

/**
 * Class m190718_114342_add_table_coordination_to_order
 */
class m190718_114342_add_table_coordination_to_order extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('order_coordination', [
            'id' => $this->primaryKey(),
            'coordination_id' => $this->integer(),
            'order_id' => $this->integer(),
            'company_id' => $this->integer(),
            'name' => $this->string(),
            'sum' => $this->decimal(22, 2),
            'type' => $this->integer(),
            'status' => $this->integer()->defaultValue(0),
            'next_step' => $this->integer(),
            'order' => $this->integer(),
            'all_roles' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('order_coordination_roles', [
            'id' => $this->primaryKey(),
            'coordination_id' => $this->integer(),
            'order_id' => $this->integer(),
            'role' => $this->string()
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->createTable('order_coordination_users', [
            'id' => $this->primaryKey(),
            'coordination_id' => $this->integer(),
            'order_id' => $this->integer(),
            'user_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('order_coordination');
        $this->dropTable('order_coordination_roles');
        $this->dropTable('order_coordination_users');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190718_114342_add_table_coordination_to_order cannot be reverted.\n";

        return false;
    }
    */
}
