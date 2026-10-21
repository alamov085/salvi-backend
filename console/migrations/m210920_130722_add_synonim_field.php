<?php

use yii\db\Migration;

/**
 * Class m210920_130722_add_synonim_field
 */
class m210920_130722_add_synonim_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('product', 'synonym', $this->text()->defaultValue(null));


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210920_130722_add_synonim_field cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210920_130722_add_synonim_field cannot be reverted.\n";

        return false;
    }
    */
}
