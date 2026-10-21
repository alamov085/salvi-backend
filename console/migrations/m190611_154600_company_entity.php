<?php

use yii\db\Migration;

/**
 * Class m190611_154600_company_entity
 */
class m190611_154600_company_entity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('company_entity', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'short_name' => $this->string(),
            'full_name' => $this->string(),
            'address' => $this->string(),
            'bank_number' => $this->string(),
            'bank_name' => $this->string(),
            'mfo' => $this->string(),
            'edrpou' => $this->string(),
            'inn' => $this->string(),
            'tax_payer' => $this->string(),
            'status' => $this->integer()->defaultValue(0),
            'from_date' => $this->integer(),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('company_entity');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190611_154600_company_entity cannot be reverted.\n";

        return false;
    }
    */
}
