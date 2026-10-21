<?php

use yii\db\Migration;

/**
 * Class m191202_152900_zerofill_to_zip
 */
class m191202_152900_zerofill_to_zip extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('locations', 'zip_code', $this->string(5));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('locations', 'zip_code', 'INT(11) NULL DEFAULT NULL');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191202_152900_zerofill_to_zip cannot be reverted.\n";

        return false;
    }
    */
}
