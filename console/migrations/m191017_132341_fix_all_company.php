<?php

use yii\db\Migration;

/**
 * Class m191017_132341_fix_all_company
 */
class m191017_132341_fix_all_company extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'all_company', 'int NOT NULL DEFAULT 0');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('user', 'all_company', 'int NOT NULL DEFAULT 0');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191017_132341_fix_all_company cannot be reverted.\n";

        return false;
    }
    */
}
