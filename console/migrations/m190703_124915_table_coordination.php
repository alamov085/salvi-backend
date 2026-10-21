<?php

use yii\db\Migration;

/**
 * Class m190703_124915_table_coordination
 */
class m190703_124915_table_coordination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('coordination', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'name' => $this->string(),
            'sum' => $this->decimal(22, 2),
            'type' => $this->integer(),
            'status' => $this->integer()->defaultValue(0),
            'order' => $this->integer(),
            'all_roles' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('coordination_roles', [
            'id' => $this->primaryKey(),
            'coordination_id' => $this->integer(),
            'role' => $this->string()
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->createTable('coordination_users', [
            'id' => $this->primaryKey(),
            'coordination_id' => $this->integer(),
            'user_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('coordination');
        $this->dropTable('coordination_roles');
        $this->dropTable('coordination_users');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190703_124915_table_coordination cannot be reverted.\n";

        return false;
    }
    */
}
