<?php

use yii\db\Migration;

/**
 * Class m200130_104926_change_category_structure
 */
class m200130_104926_change_category_structure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('category', 'is_props_value');
        $this->dropColumn('category', 'is_value');
        $this->dropColumn('category', 'is_props');
        $this->addColumn('category', 'type', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('category', 'is_props_value', $this->boolean());
        $this->addColumn('category', 'is_props', $this->boolean());
        $this->addColumn('category', 'is_value', $this->boolean());
         $this->dropColumn('category', 'type');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200130_104926_change_category_structure cannot be reverted.\n";

        return false;
    }
    */
}
