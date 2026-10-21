<?php

use yii\db\Migration;

/**
 * Class m190726_090935_add_row_to_table_limitation
 */
class m190726_090935_add_row_to_table_limitation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('catalog_limitation', 'for_show', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('catalog_limitation', 'for_show');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190726_090935_add_row_to_table_limitation cannot be reverted.\n";

        return false;
    }
    */
}
