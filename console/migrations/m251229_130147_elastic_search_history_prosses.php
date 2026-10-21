<?php

use yii\db\Migration;

/**
 * Class m251229_130147_elastic_search_history_prosses
 */
class m251229_130147_elastic_search_history_prosses extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('elastic_search_history_prosses', [
            'id' => $this->primaryKey(),
            'query' => $this->string()->defaultValue(null),
            'type' => $this->string()->defaultValue(null),
            'user_create' => $this->integer()->defaultValue(null),
            'user_update' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251229_130147_elastic_search_history_prosses cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251229_130147_elastic_search_history_prosses cannot be reverted.\n";

        return false;
    }
    */
}
