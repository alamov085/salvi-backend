<?php

use yii\db\Migration;

/**
 * Class m190528_125051_currency
 */
class m190528_125051_currency extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('currency', [
           'id'=>$this->primaryKey(),
           'code'=>$this->string(),
           'name'=>$this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('currency');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190528_125051_currency cannot be reverted.\n";

        return false;
    }
    */
}
