<?php

use yii\db\Migration;

/**
 * Class m200102_111231_add_config
 */
class m200102_111231_add_config extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $model = new \common\models\Config([
            'params' => \common\models\Config::ADMIN_TOTAL_ORDERS,
            'value' => 1000000
        ]);
        $model->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \common\models\Config::deleteAll(['params' => \common\models\Config::ADMIN_TOTAL_ORDERS]);
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200102_111231_add_config cannot be reverted.\n";

        return false;
    }
    */
}
