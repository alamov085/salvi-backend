<?php

use yii\db\Migration;

/**
 * Class m190618_120233_company_entity_location
 */
class m190618_120233_company_entity_location extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('company_entity_location', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->defaultValue(0),
            'location_id' => $this->integer()->defaultValue(0)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('company_entity_location');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190618_120233_company_entity_location cannot be reverted.\n";

        return false;
    }
    */
}
