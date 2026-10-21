<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "order_coordination_roles".
 *
 * @property int $id
 * @property int $coordination_id
 * @property int $order_id
 * @property string $role
 */
class OrderCoordinationRoles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_coordination_roles';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['coordination_id', 'order_id'], 'integer'],
            [['role'], 'string', 'max' => 255],
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
            'role' => 'Role',
        ];
    }
}
