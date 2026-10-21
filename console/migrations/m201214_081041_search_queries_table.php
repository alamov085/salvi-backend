<?php

use yii\db\Schema;
use yii\db\Migration;

/**
 * Class m201214_081041_search_queries_table
 */
class m201214_081041_search_queries_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('search_queries', [
            'id' => $this->primaryKey(),
            'query_string' =>  $this->string()->notNull(),
            'created_at' => $this->integer(11),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('company_entity');

//        echo "m201214_081041_search_queries_table cannot be reverted.\n";
//
//        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201214_081041_search_queries_table cannot be reverted.\n";

        return false;
    }
    */
}
