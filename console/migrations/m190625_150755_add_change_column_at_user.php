<?php

use yii\db\Migration;

/**
 * Class m190625_150755_add_change_column_at_user
 */
class m190625_150755_add_change_column_at_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('user', 'currency_id');
        $this->addColumn('user', 'currency_id', $this->integer()->defaultValue(1));
        $model = new \common\models\Currency();
        $model->name = 'Гривня';
        $model->code = 'UAH';
        $model->save();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'currency_id');
        $this->addColumn('user', 'currency_id', $this->integer());
        Yii::$app->db->createCommand()->truncateTable(\common\models\Currency::tableName())->execute();
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190625_150755_add_change_column_at_user cannot be reverted.\n";

        return false;
    }
    */
}
