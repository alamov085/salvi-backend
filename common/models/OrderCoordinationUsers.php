<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_coordination_users".
 *
 * @property int $id
 * @property int $coordination_id
 * @property int $order_id
 * @property int $user_id
 */
class OrderCoordinationUsers extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_coordination_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coordination_id', 'order_id', 'user_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coordination_id' => 'Coordination ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
        ];
    }
}
