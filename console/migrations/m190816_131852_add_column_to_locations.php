<?php

use yii\db\Migration;

/**
 * Class m190816_131852_add_column_to_locations
 */
class m190816_131852_add_column_to_locations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('locations', 'allCompanies', $this->smallInteger()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('locations', 'allCompanies');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190816_131852_add_column_to_locations cannot be reverted.\n";

        return false;
    }
    */
}
