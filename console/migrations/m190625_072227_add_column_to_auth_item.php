<?php

use yii\db\Migration;

/**
 * Class m190625_072227_add_column_to_auth_item
 */
class m190625_072227_add_column_to_auth_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('auth_item', 'parent_id', $this->integer()->defaultValue(0));
        $this->addColumn('auth_item', 'status', $this->integer()->defaultValue(1));
        $this->createTable('auth_item_parent', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('auth_item', 'parent_id');
        $this->dropColumn('auth_item', 'status');
        $this->dropTable('auth_item_parent');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190625_072227_add_column_to_auth_item cannot be reverted.\n";

        return false;
    }
    */
}
