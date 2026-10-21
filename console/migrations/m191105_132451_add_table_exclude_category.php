<?php

use yii\db\Migration;

/**
 * Class m191105_132451_add_table_exclude_category
 */
class m191105_132451_add_table_exclude_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('category_exclude', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer()->notNull()->defaultValue(0),
            'category_uuid' => $this->string(),
            'category_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('category_exclude');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191105_132451_add_table_exclude_category cannot be reverted.\n";

        return false;
    }
    */
}
