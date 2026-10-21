<?php

use yii\db\Migration;

/**
 * Class m190802_081510_add_table_contract_location
 */
class m190802_081510_add_table_contract_location extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contract_location', [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer(),
            'location_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contract_location');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190802_081510_add_table_contract_location cannot be reverted.\n";

        return false;
    }
    */
}
