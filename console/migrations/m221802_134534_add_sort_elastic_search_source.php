<?php

use yii\db\Migration;

/**
 * Class m220802_134534_elastic_search_source
 */
class m221802_134534_add_sort_elastic_search_source extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('elastic_search_source', 'sort',$this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m220802_134534_elastic_search_source cannot be reverted.\n";

        return false;
    }
    */
}
