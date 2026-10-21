<?php

use yii\db\Migration;

/**
 * Class m191011_140430_add_add_type_to_coordination
 */
class m191011_140430_add_add_type_to_coordination extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('coordination', 'additional_type', $this->integer()->defaultValue(1));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('coordination', 'additional_type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191011_140430_add_add_type_to_coordination cannot be reverted.\n";

        return false;
    }
    */
}
