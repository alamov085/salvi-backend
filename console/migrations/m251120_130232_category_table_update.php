<?php

use yii\db\Migration;

/**
 * Class m251120_130232_category_table_update
 */
class m251120_130232_category_table_update extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('category', 'img_path', $this->string()->defaultValue(null));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m251120_130232_category_table_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251120_130232_category_table_update cannot be reverted.\n";

        return false;
    }
    */
}
