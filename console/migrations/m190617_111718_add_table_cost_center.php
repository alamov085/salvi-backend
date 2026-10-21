<?php

use yii\db\Migration;

/**
 * Class m190617_111718_add_table_cost_center
 */
class m190617_111718_add_table_cost_center extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cost_center', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'location_id' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cost_center');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190617_111718_add_table_cost_center cannot be reverted.\n";

        return false;
    }
    */
}
