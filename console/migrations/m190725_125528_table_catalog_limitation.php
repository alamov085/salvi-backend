<?php

use yii\db\Migration;

/**
 * Class m190725_125528_table_catalog_limitation
 */
class m190725_125528_table_catalog_limitation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('catalog_limitation', [
            'id' => $this->primaryKey(),
            'company_id' => $this->integer(),
            'category_id' => $this->integer(),
            'category_uuid' => $this->string(),
            'markup' => $this->decimal(6, 2)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('catalog_limitation');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190725_125528_table_catalog_limitation cannot be reverted.\n";

        return false;
    }
    */
}
