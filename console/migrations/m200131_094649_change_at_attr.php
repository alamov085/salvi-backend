<?php

use yii\db\Migration;

/**
 * Class m200131_094649_change_at_attr
 */
class m200131_094649_change_at_attr extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('attribute', 'category_id', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn('attribute', 'uuid', $this->string());
        $this->addColumn('attribute_value', 'uuid', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('attribute', 'category_id');
        $this->dropColumn('attribute', 'uuid');
        $this->dropColumn('attribute_value', 'uuid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200131_094649_change_at_attr cannot be reverted.\n";

        return false;
    }
    */
}
